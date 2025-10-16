<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use ReflectionAttribute;
use ReflectionClass;
use Sapin\Engine\Attribute\ComponentLoader;
use Sapin\Engine\Parser\Template\Stage2\Node as Stage2;
use Sapin\Engine\Parser\Template\Stage3\Node\UseNode;
use Sapin\Engine\SapinException;
use function array_key_exists;
use function sprintf;

abstract class ComponentMetadataParser
{
    /**
     * @throws SapinException
     * @return ComponentMetadata[]
     */
    public static function parseComponentMetadata(Stage2\PairedTagNode $templateNode): array
    {
        return array_map(
            [self::class, 'getComponentMetadata'],
            self::parseUses($templateNode),
        );
    }

    /**
     * @throws SapinException
     * @return UseNode[]
     */
    private static function parseUses(Stage2\PairedTagNode $templateNode): array
    {
        /** @var ?Stage2\DynamicAttributeNode $usesAttribute */
        $usesAttribute = null;

        foreach ($templateNode->attributes as $templateNodeAttribute) {
            if ($templateNodeAttribute instanceof Stage2\DynamicAttributeNode
                && $templateNodeAttribute->name === 'uses'
            ) {
                $usesAttribute = $templateNodeAttribute;
                break;
            }
        }

        if ($usesAttribute === null) {
            return [];
        }

        $uses = preg_split('/\s*,\s*/', trim($usesAttribute->expression, ", \n\r\t\v\0"))
            ?: throw new SapinException('Invalid uses value');

        $indexedUseNodes = [];
        foreach ($uses as $use) {
            $useParts = preg_split('/\s+as\s+/', $use)
                ?: throw new SapinException(sprintf('Invalid use expression: %s', $use));

            [$classFqn, $componentName] = array_pad($useParts, 2, null);
            $componentName ??= basename(str_replace('\\', '/', $classFqn));

            if (array_key_exists($componentName, $indexedUseNodes)) {
                throw new SapinException(sprintf(
                    'Another component named "%s" was previously imported. Consider using the aliasing syntax',
                    $componentName,
                ));
            }

            $indexedUseNodes[$componentName] = new UseNode($componentName, $classFqn);
        }

        return array_values($indexedUseNodes);
    }

    /** @throws SapinException */
    private static function getComponentMetadata(UseNode $useNode): ComponentMetadata
    {
        if (!class_exists($useNode->classFqn)) {
            throw new SapinException(sprintf('Unknown component class "%s"', $useNode->classFqn));
        }

        $class = new ReflectionClass($useNode->classFqn);

        $loaders = array_map(
            function (ReflectionAttribute $attribute) {
                /** @var ComponentLoader $attributeInstance */
                $attributeInstance = $attribute->newInstance();

                if (!class_exists($attributeInstance->classFqn)) {
                    throw new SapinException(sprintf('Unknown component loader class "%s"', $attributeInstance->classFqn));
                }

                $class = new ReflectionClass($attributeInstance->classFqn);

                return new ComponentLoaderMetadata(
                    classFqn: $attributeInstance->classFqn,
                    parameters: $class->getConstructor()?->getParameters() ?? [],
                );
            },
            $class->getAttributes(ComponentLoader::class),
        );

        return new ComponentMetadata(
            name: $useNode->componentName,
            classFqn: $useNode->classFqn,
            parameters: $class->getConstructor()?->getParameters() ?? [],
            loaders: $loaders,
        );
    }
}
