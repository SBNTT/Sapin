<?php

namespace Sapin\Engine;

use CallbackFilterIterator;
use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use ReflectionObject;
use Sapin\Engine\Ast\Compiler;
use Sapin\Engine\Ast\Parser\ComponentNodeParser;
use SplFileInfo;
use Stringable;
use Throwable;

abstract class Sapin
{
    private static ?string $cacheDirectory = null;
    private static bool $disableIncrementalCompilation = false;

    private const COMPONENT_FILE_EXTENSION = 'phtml';

    public static function configure(
        string $cacheDirectory,
        bool   $disableIncrementalCompilation = false
    ): void {
        self::$cacheDirectory = $cacheDirectory;
        self::$disableIncrementalCompilation = $disableIncrementalCompilation;

        if (!in_array([self::class, 'autoload'], spl_autoload_functions())) {
            spl_autoload_register([self::class, 'autoload']);
        }
    }

    /**
     * @throws SapinException
     */
    public static function render(
        object $component,
        ?callable $slotRenderer = null,
        RenderingContext $context = new RenderingContext(),
    ): void {
        if (!($component instanceof ComponentInterface)) {
            throw new SapinException(sprintf(
                'This is not a valid component to render: "%s". Subtype of Sapin\\ComponentInterface expected',
                get_class($component),
            ));
        }

        $component->render($context, $slotRenderer);

        if (!in_array($component::class, $context->renderedComponentStyles, true)) {
            $component->renderStyles($context);
            $context->renderedComponentStyles[] = $component::class;
        }
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

    /**
     * @throws SapinException
     */
    public static function warmUpCache(): void
    {
        foreach (self::getAllAutoloadableComponentFilePathsGenerator() as $componentFileInfo) {
            $componentFilePath = $componentFileInfo->getPathname();
            $compiledComponentFilePath = self::getCompiledComponentFilePath($componentFilePath);
            self::compile($componentFilePath, $compiledComponentFilePath);
        }
    }

    public static function echo(string|int|float|bool|Stringable $value): void
    {
        echo $value;
    }

    /**
     * @throws SapinException
     */
    private static function autoload(string $class): void
    {
        if (($componentFilePath = self::resolveComponentClassFilePath($class)) === null) {
            return;
        }

        $compiledComponentFilePath = self::getCompiledComponentFilePath($componentFilePath);
        self::compile($componentFilePath, $compiledComponentFilePath);
        require_once $compiledComponentFilePath;
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
            @require_once 'html5_parser_monkey_patch.php';

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

    private static function resolveComponentClassFilePath(string $class): ?string
    {
        foreach (ClassLoader::getRegisteredLoaders() as $classLoader) {
            try {
                $filePath = (new ReflectionObject($classLoader))
                    ->getMethod('findFileWithExtension')
                    ->invoke($classLoader, $class, '.' . self::COMPONENT_FILE_EXTENSION);

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
    private static function getCompiledComponentFilePath(string $componentFilePath): string
    {
        return implode('/', [
            rtrim(self::getCacheDirectory(), '/'),
            md5($componentFilePath) . '.php'
        ]);
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

    /**
     * @return Generator<SplFileInfo>
     */
    private static function getAllAutoloadableComponentFilePathsGenerator(): Generator
    {
        foreach (ClassLoader::getRegisteredLoaders() as $classLoader) {
            foreach ([...$classLoader->getPrefixesPsr4(), ...$classLoader->getPrefixes()] as $paths) {
                foreach ($paths as $path) {
                    yield from self::getAllComponentFilesOfPathGenerator($path);
                }
            }

            foreach ($classLoader->getClassMap() as $path) {
                $fileInfo = new SplFileInfo($path);
                if (self::isComponentFile($fileInfo)) {
                    yield $fileInfo;
                }
            }
        }

        yield from [];
    }

    /**
     * @return Generator<SplFileInfo>
     */
    private static function getAllComponentFilesOfPathGenerator(string $path): Generator
    {
        if (!is_dir($path)) {
            return;
        }

        /** @var iterable<SplFileInfo> $iterator */
        $iterator = new CallbackFilterIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            [self::class, 'isComponentFile']
        );

        yield from $iterator;
    }

    private static function isComponentFile(SplFileInfo $file): bool
    {
        return $file->isFile() && $file->getExtension() === self::COMPONENT_FILE_EXTENSION;
    }
}
