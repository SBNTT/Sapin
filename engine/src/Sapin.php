<?php

namespace Sapin;

use Composer\Autoload\ClassLoader;
use ReflectionException;
use ReflectionObject;
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
        spl_autoload_register(
            /** @throws SapinException */
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
     * @throws SapinException
     */
    public static function render(object $component, ?callable $slotRenderer = null): void
    {
        if (!($component instanceof ComponentInterface)) {
            throw new SapinException(sprintf(
                'This is not a valid component to render: "%s". Subtype of Sapin\\ComponentInterface expected',
                get_class($component),
            ));
        }

        $component->render($slotRenderer);
    }

    /**
     * @throws SapinException
     */
    public static function renderToString(object $component): string
    {
        ob_start();
        self::render($component);
        return ob_get_clean() ?: throw new SapinException('Failed to read output buffer contents');
    }

    public static function echo(string|int|float|bool|Stringable $value): void
    {
        echo $value;
    }

    /**
     * @throws SapinException
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

        try {
            $componentNode = (new ComponentNodeParser())->parse($componentFilePath);
        } catch (SapinException $e) {
            throw new SapinException(
                sprintf('Failed to compile "%s" file', $componentFilePath),
                previous: $e,
            );
        } catch (Throwable $t) {
            throw new SapinException(
                sprintf('Unexpected exception during compilation of "%s" file', $componentFilePath),
                previous: $t,
            );
        }

        $compiler = new Compiler();
        $componentNode->compile($compiler);

        !is_dir(self::getCacheDirectory()) && mkdir(self::getCacheDirectory(), recursive: true);
        file_put_contents($compiledComponentFilePath, $compiler->getOut());
    }

    /**
     * @throws SapinException
     */
    private static function getCacheDirectory(): string
    {
        return self::$cacheDirectory ?? throw new SapinException(
            'Failed to get cache directory. Sapin::configure function must be called first',
        );
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
            } catch (ReflectionException) {
            }
        }

        return null;
    }

    /**
     * @throws SapinException
     */
    private static function resolveCompiledComponentFilePath(string $componentFqn): string
    {
        return implode('/', [
            rtrim(self::getCacheDirectory(), '/'),
            md5($componentFqn) . '.php'
        ]);
    }
}
