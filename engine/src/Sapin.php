<?php

namespace Sapin;

use Composer\Autoload\ClassLoader;
use Exception;
use ReflectionException;
use ReflectionObject;
use Sapin\Ast\Compiler;
use Sapin\Ast\Parser\ComponentNodeParser;
use Stringable;

abstract class Sapin
{
    private static ?string $cacheDirectory = null;
    private static bool $disableIncrementalCompilation = false;

    public static function configure(
        string $cacheDirectory,
        bool   $disableIncrementalCompilation = false
    ): void {
        self::$cacheDirectory = $cacheDirectory;
        self::$disableIncrementalCompilation = $disableIncrementalCompilation;
        spl_autoload_register(
            /** @throws Exception */
            static function ($class) {
                if (($componentFilePath = self::resolveComponentClassFilePath($class)) === null) {
                    return;
                }

                $compiledComponentFilePath = self::resolveCompiledComponentFilePath($class);
                self::compile($componentFilePath, $compiledComponentFilePath);
                require_once $compiledComponentFilePath;
            },
        );
    }

    /**
     * @throws Exception
     */
    public static function render(object $component, ?callable $slotRenderer = null): void
    {
        if (!($component instanceof ComponentInterface)) {
            throw new Exception(sprintf('This is not a valid component to render: "%s"', get_class($component)));
        }

        $component->render($slotRenderer);
    }

    /**
     * @throws Exception
     */
    public static function renderToString(object $component): string
    {
        ob_start();
        self::render($component);
        return ob_get_clean() ?: throw new Exception('Failed to read output buffer contents');
    }

    public static function echo(string|int|float|bool|Stringable $value): void
    {
        echo $value;
    }

    /**
     * @throws Exception
     */
    private static function compile(
        string $componentFilePath,
        string $compiledComponentFilePath,
    ): void {
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
     * @throws Exception
     */
    private static function getCacheDirectory(): string
    {
        return self::$cacheDirectory ?? throw new Exception('Sapin::configure function must be called first');
    }

    private static function resolveComponentClassFilePath(string $class): ?string
    {
        foreach (ClassLoader::getRegisteredLoaders() as $autoloader) {
            try {
                $filePath = (new ReflectionObject($autoloader))
                    ->getMethod('findFileWithExtension')
                    ->invoke($autoloader, $class, '.sapin');

                if (is_string($filePath)) {
                    return $filePath;
                }
            } catch (ReflectionException $e) {
            }
        }

        return null;
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
