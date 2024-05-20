<?php

namespace Sapin;

use Closure;
use Composer\Autoload\ClassLoader;
use Exception;
use Sapin\Ast\Compiler;
use Sapin\Ast\Parser\ComponentNodeParser;
use Stringable;
use Throwable;

abstract class Sapin
{
    private static ?string $cacheDirectory = null;
    private static bool $disableIncrementalCompilation = false;

    public static function configure(
        string $cacheDirectory,
        bool $disableIncrementalCompilation = false
    ): void {
        self::$cacheDirectory = $cacheDirectory;
        self::$disableIncrementalCompilation = $disableIncrementalCompilation;

        spl_autoload_register(static function ($class) {
            try {
                if (file_exists(($path = self::resolveCompiledComponentFilePath($class)))) {
                    require $path;
                }
            } catch (Throwable) {
            }
        }, prepend: true);
    }

    /**
     * @template T of object
     * @param class-string<T> $componentFqn
     * @param Closure(): T $initializer
     * @throws Exception
     */
    public static function compileAndRender(string $componentFqn, Closure $initializer): void
    {
        self::compile($componentFqn);
        self::render($initializer);
    }

    /**
     * @template T of object
     * @param class-string<T> $componentFqn
     * @param Closure(): T $initializer
     * @throws Exception
     */
    public static function compileAndRenderToString(string $componentFqn, Closure $initializer): string
    {
        self::compile($componentFqn);
        return self::renderToString($initializer);
    }

    /**
     * @template T of object
     * @param class-string<T> $componentFqn
     * @throws Exception
     */
    public static function compile(string $componentFqn): void
    {
        $componentFilePath = self::resolveClassFilePath($componentFqn);
        $compiledComponentFilePath = self::resolveCompiledComponentFilePath($componentFqn);

        if (!self::$disableIncrementalCompilation
            && file_exists($compiledComponentFilePath)
            && filemtime($compiledComponentFilePath) > filemtime($componentFilePath)
        ) {
            return;
        }

        $contents = file_get_contents($componentFilePath)
            ?: throw new Exception('Failed to read contents of "' . $componentFilePath . '"');

        $componentNode = (new ComponentNodeParser())->parse($contents);

        $compiler = new Compiler();
        $componentNode->compile($compiler);

        !is_dir(self::getCacheDirectory()) && mkdir(self::getCacheDirectory(), recursive: true);
        file_put_contents($compiledComponentFilePath, $compiler->getOut());
    }

    /**
     * @template T of object
     * @param Closure(): T $initializer
     * @throws Exception
     */
    public static function render(Closure $initializer): void
    {
        $component = $initializer();

        if (!($component instanceof ComponentInterface)) {
            throw new Exception(sprintf('This is not a valid component to render: "%s"', get_class($component)));
        }

        $component->render();
    }

    /**
     * @template T of object
     * @param Closure(): T $initializer
     * @throws Exception
     */
    public static function renderToString(Closure $initializer): string
    {
        ob_start();
        self::render($initializer);
        return ob_get_clean() ?: throw new Exception('Failed to read output buffer contents');
    }

    public static function echo(string|int|float|bool|Stringable $value): void
    {
        echo $value;
    }

    /**
     * @throws Exception
     */
    private static function getCacheDirectory(): string
    {
        return self::$cacheDirectory ?? throw new Exception('Sapin::configure function must be called first');
    }

    /**
     * @param class-string $class
     * @throws Exception
     */
    private static function resolveClassFilePath(string $class): string
    {
        foreach (ClassLoader::getRegisteredLoaders() as $autoloader) {
            if (is_string($path = $autoloader->findFile($class))) {
                return $path;
            }
        }

        throw new Exception(sprintf('class "%s" file cannot be resolved', $class));
    }

    /**
     * @throws Exception
     */
    private static function resolveCompiledComponentFilePath(string $ComponentFqn): string
    {
        return implode('/', [
            rtrim(self::getCacheDirectory(), '/'),
            md5($ComponentFqn) . '.php'
        ]);
    }
}
