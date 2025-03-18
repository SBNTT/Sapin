<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use ReflectionClass;
use ReflectionParameter;
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
        $usesAttribute = array_filter(
            $templateNode->attributes,
            fn ($attr) => $attr instanceof Stage2\DynamicAttributeNode && $attr->name === 'uses',
        );

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
        // Usage of ReflectionClass may cause a sub compilation of the `$componentFqn` component
        $componentConstructorParameters = array_map(
            fn (ReflectionParameter $parameter) => new ComponentProperty(
                name: $parameter->getName(),
                type: (string) $parameter->getType(),
            ),
            self::getComponentConstructorParameters($useNode->classFqn),
        );

        return new ComponentMetadata(
            name: $useNode->componentName,
            classFqn: $useNode->classFqn,
            properties: $componentConstructorParameters,
        );
    }

    /**
     * @throws SapinException
     * @return ReflectionParameter[]
     */
    private static function getComponentConstructorParameters(string $classFqn): array
    {
        if (!class_exists($classFqn)) {
            throw new SapinException(sprintf('Unknown component class "%s"', $classFqn));
        }

        $class = new ReflectionClass($classFqn);

        return $class->getConstructor()?->getParameters() ?? [];
    }
}
