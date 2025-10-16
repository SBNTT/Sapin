<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use ReflectionParameter;
use Sapin\Engine\Parser\Template\Stage2\Node as Stage2;
use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;
use Sapin\Engine\SapinException;
use function count;
use function in_array;
use function sprintf;

final class Stage3Parser
{
    /**
     * @param Stage2\AbstractNode[] $nodes
     * @throws SapinException
     * @return Stage3\AbstractNode[]
     */
    public static function parseStage2Nodes(array $nodes): array
    {
        $templateNode = $nodes[0] ?? null;
        if (!($templateNode instanceof Stage2\PairedTagNode) || $templateNode->name !== 'template') {
            throw new SapinException('Invalid root template node');
        }

        $componentsMetadata = ComponentMetadataParser::parseComponentMetadata($templateNode);

        return array_map(
            fn ($node) => self::parseStage2Node($node, $componentsMetadata),
            $templateNode->children,
        );
    }

    /**
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function parseStage2Node(
        Stage2\AbstractNode $node,
        array $componentsMetadata,
    ): Stage3\AbstractNode {
        return match ($node::class) {
            Stage2\CommentNode::class => self::parseCommentNode($node),
            Stage2\RawNode::class => self::parseRawNode($node),
            Stage2\InterpolationNode::class => self::parseInterpolationNode($node),
            Stage2\InlineTagNode::class => self::parseInlineTagNode($node),
            Stage2\PairedTagNode::class => self::parsePairedTagNode($node, $componentsMetadata),
            Stage2\SelfClosedTagNode::class => self::parseSelfClosedTagName($node, $componentsMetadata),
            default => throw new SapinException(sprintf('Unexpected node "%s"', $node::class)),
        };
    }

    private static function parseCommentNode(Stage2\CommentNode $node): Stage3\AbstractNode
    {
        return new Stage3\CommentNode(content: $node->content);
    }

    private static function parseRawNode(Stage2\RawNode $node): Stage3\AbstractNode
    {
        return new Stage3\RawNode(content: $node->content);
    }

    private static function parseInterpolationNode(Stage2\InterpolationNode $node): Stage3\AbstractNode
    {
        return new Stage3\InterpolationNode(expression: $node->expression);
    }

    /** @throws SapinException */
    private static function parseInlineTagNode(Stage2\InlineTagNode $node): Stage3\AbstractNode
    {
        [$attributes, $specialAttributes] = self::parseAttributesNodes($node->attributes);

        $stage3Node = new Stage3\InlineTagNode(
            name: $node->name,
            attributes: $attributes,
        );

        return self::applySpecialAttributes($stage3Node, $specialAttributes);
    }

    /**
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function parseSelfClosedTagName(
        Stage2\SelfClosedTagNode $node,
        array $componentsMetadata,
    ): Stage3\AbstractNode {
        [$attributes, $specialAttributes] = self::parseAttributesNodes($node->attributes);

        $stage3Node = self::tryParseSlotDeclarationNode($node, [])
            ?? (self::tryParseComponentCallNode($node, $attributes, [], $componentsMetadata)
            ?? new Stage3\PairedTagNode($node->name, $attributes, []));

        return self::applySpecialAttributes($stage3Node, $specialAttributes);
    }

    /**
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function parsePairedTagNode(
        Stage2\PairedTagNode $node,
        array $componentsMetadata,
    ): Stage3\AbstractNode {
        $children = array_map(
            fn ($node) => self::parseStage2Node($node, $componentsMetadata),
            $node->children,
        );

        [$attributes, $specialAttributes] = self::parseAttributesNodes($node->attributes);

        $stage3Node = self::tryParseFragmentNode($node, $children)
            ?? self::tryParseSlotDeclarationNode($node, $children)
            ?? self::tryParseComponentCallNode($node, $attributes, $children, $componentsMetadata)
            ?? new Stage3\PairedTagNode($node->name, $attributes, $children);

        return self::applySpecialAttributes($stage3Node, $specialAttributes);
    }

    /**
     * @param array<Stage2\DynamicAttributeNode|Stage2\StaticAttributeNode> $nodes
     * @throws SapinException
     * @return array{
     *     array<Stage3\DynamicAttributeNode|Stage3\StaticAttributeNode>,
     *     array<Stage3\SpecialAttributeNode>
     * }
     */
    private static function parseAttributesNodes(array $nodes): array
    {
        /** @var array<Stage3\DynamicAttributeNode|Stage3\StaticAttributeNode> $attributesNodes */
        $attributesNodes = [];

        /** @var Stage3\SpecialAttributeNode[] $specialAttributesNodes */
        $specialAttributesNodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof Stage2\DynamicAttributeNode) {
                if (($specialAttributeKind = SpecialAttribute::tryFrom($node->name)) !== null) {
                    $specialAttributesNodes[] = new Stage3\SpecialAttributeNode(
                        kind: $specialAttributeKind,
                        expression: $node->expression,
                        delimiter: $node->delimiter,
                    );
                } else {
                    $attributesNodes[] = new Stage3\DynamicAttributeNode(
                        name: $node->name,
                        expression: $node->expression,
                        delimiter: $node->delimiter,
                    );
                }
            } elseif ($node instanceof Stage2\StaticAttributeNode) {
                $attributesNodes[] = new Stage3\StaticAttributeNode(
                    name: $node->name,
                    children: self::convertStage2AttributeChildrenToStage3Ones($node->children),
                    delimiter: $node->delimiter,
                );
            }
        }

        AttributesListChecker::checkAttributes($attributesNodes, $specialAttributesNodes);

        return [$attributesNodes, $specialAttributesNodes];
    }

    /** @param Stage3\AbstractNode[] $children */
    private static function tryParseFragmentNode(
        Stage2\PairedTagNode $node,
        array $children,
    ): ?Stage3\AbstractNode {
        return $node->name === 'fragment'
            ? new Stage3\FragmentNode($children)
            : null;
    }

    /** @param Stage3\AbstractNode[] $children */
    private static function tryParseSlotDeclarationNode(
        Stage2\PairedTagNode|Stage2\SelfClosedTagNode $node,
        array $children,
    ): ?Stage3\SlotDeclarationNode {
        // TODO: produce an error if name is slot and :name attribute is missing ?
        if ($node->name === 'slot'
            && ($nameAttribute = self::tryGetStage2DynamicAttributeByName($node->attributes, 'name')) !== null
        ) {
            return new Stage3\SlotDeclarationNode(
                $nameAttribute->expression,
                $children,
            );
        }

        return null;
    }

    /**
     * @param Stage3\AbstractNode[] $children
     * @param array<Stage3\StaticAttributeNode|Stage3\DynamicAttributeNode> $attributes
     * @param ComponentMetadata[] $componentsMetadata
     *
     * @throws SapinException
     */
    private static function tryParseComponentCallNode(
        Stage2\PairedTagNode|Stage2\SelfClosedTagNode $node,
        array $attributes,
        array $children,
        array $componentsMetadata,
    ): ?Stage3\ComponentCallNode {
        if (($componentMetadata = self::tryGetComponentMetadataByName($node->name, $componentsMetadata)) === null) {
            return null;
        }

        if (self::attributesCoverRequiredParameters($attributes, $componentMetadata->parameters)) {
            [$props, $extraAttributes] = self::splitAttributesByParameters($attributes, $componentMetadata->parameters);

            return new Stage3\ComponentCallNode(
                classFqn: $componentMetadata->classFqn,
                props: $props,
                attributes: $extraAttributes,
                children: $children,
            );
        }

        foreach ($componentMetadata->loaders as $loader) {
            if (!self::attributesCoverRequiredParameters($attributes, $loader->parameters)) {
                continue;
            }

            [$props, $extraAttributes] = self::splitAttributesByParameters($attributes, $loader->parameters);

            return new Stage3\ComponentCallNode(
                classFqn: $loader->classFqn,
                props: $props,
                attributes: $extraAttributes,
                children: $children,
            );
        }

        throw new SapinException(sprintf('No way found to create "%s" component', $node->name));
    }

    /**
     * @param array<Stage3\StaticAttributeNode|Stage3\DynamicAttributeNode> $attributes
     * @param ReflectionParameter[] $parameters
     */
    private static function attributesCoverRequiredParameters(array $attributes, array $parameters): bool
    {
        $attributeNames = array_map(fn ($a) => $a->name, $attributes);

        foreach ($parameters as $parameter) {
            if ($parameter->isOptional()) {
                continue;
            }

            if (!in_array($parameter->name, $attributeNames, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<Stage3\StaticAttributeNode|Stage3\DynamicAttributeNode> $attributes
     * @param ReflectionParameter[] $parameters
     * @return array{
     *      array<Stage3\StaticComponentPropertyNode|Stage3\DynamicComponentPropertyNode>,
     *      array<Stage3\StaticAttributeNode|Stage3\DynamicAttributeNode>
     *  }
     */
    private static function splitAttributesByParameters(array $attributes, array $parameters): array
    {
        $props = [];

        foreach ($attributes as $key => $attribute) {
            foreach ($parameters as $parameter) {
                if ($attribute->name === $parameter->name) {
                    if ($attribute instanceof Stage3\StaticAttributeNode) {
                        $props[] = new Stage3\StaticComponentPropertyNode(
                            name: $attribute->name,
                            children: $attribute->children,
                            // TODO: don't use string type
                            type: (string) $parameter->getType(),
                        );
                    } elseif ($attribute instanceof Stage3\DynamicAttributeNode) {
                        $props[] = new Stage3\DynamicComponentPropertyNode(
                            name: $attribute->name,
                            expression: $attribute->expression,
                            // TODO: don't use string type
                            type: (string) $parameter->getType(),
                        );
                    }

                    unset($attributes[$key]);
                    break;
                }
            }
        }

        return [$props, array_values($attributes)];
    }

    /** @param Stage3\SpecialAttributeNode[] $specialAttributes */
    private static function applySpecialAttributes(
        Stage3\AbstractNode $node,
        array $specialAttributes,
    ): Stage3\AbstractNode {
        for ($i = count($specialAttributes) - 1; $i >= 0; --$i) {
            $attribute = $specialAttributes[$i];
            if (!($attribute instanceof Stage3\SpecialAttributeNode)) {
                continue;
            }

            $node = match ($attribute->kind) {
                SpecialAttribute::IF => new Stage3\IfNode($attribute->expression, $node),
                SpecialAttribute::ELSE_IF => new Stage3\ElseIfNode($attribute->expression, $node),
                SpecialAttribute::ELSE => new Stage3\ElseNode($node),
                SpecialAttribute::FOREACH => new Stage3\ForEachNode($attribute->expression, $node),
                SpecialAttribute::FOR => new Stage3\ForNode($attribute->expression, $node),
                SpecialAttribute::SLOT => new Stage3\SlotContentNode($attribute->expression, $node),
            };
        }

        return $node;
    }

    /**
     * @param array<Stage2\RawNode|Stage2\InterpolationNode> $nodes
     * @return array<Stage3\RawNode|Stage3\InterpolationNode>
     */
    private static function convertStage2AttributeChildrenToStage3Ones(array $nodes): array
    {
        return array_map(
            fn ($node) => match ($node::class) {
                Stage2\RawNode::class => new Stage3\RawNode($node->content),
                Stage2\InterpolationNode::class => new Stage3\InterpolationNode($node->expression),
            },
            $nodes,
        );
    }

    /** @param array<Stage2\DynamicAttributeNode|Stage2\StaticAttributeNode> $attributes */
    private static function tryGetStage2DynamicAttributeByName(
        array $attributes,
        string $name,
    ): ?Stage2\DynamicAttributeNode {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Stage2\DynamicAttributeNode && $attribute->name === $name) {
                return $attribute;
            }
        }

        return null;
    }

    /** @param ComponentMetadata[] $componentsMetadata */
    private static function tryGetComponentMetadataByName(
        string $name,
        array $componentsMetadata,
    ): ?ComponentMetadata {
        foreach ($componentsMetadata as $componentMetadata) {
            if ($componentMetadata->name === $name) {
                return $componentMetadata;
            }
        }

        return null;
    }
}
