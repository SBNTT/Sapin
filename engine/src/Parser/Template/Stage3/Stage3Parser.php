<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use Sapin\Engine\Parser\Template\Stage2\Node as Stage2;
use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;
use Sapin\Engine\SapinException;
use function count;
use function in_array;
use function sprintf;

final class Stage3Parser
{
    private const RESERVED_DYNAMIC_ATTRIBUTES = [
        'foreach',
        'for',
        'if',
        'else-if',
        'else',
        'slot',
        'name',
    ];

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

    private static function parseInlineTagNode(Stage2\InlineTagNode $node): Stage3\AbstractNode
    {
        return new Stage3\InlineTagNode(
            name: $node->name,
            attributes: self::parseAttributesNodes($node->attributes),
        );
    }

    /**
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function parseSelfClosedTagName(
        Stage2\SelfClosedTagNode $node,
        array $componentsMetadata,
    ): Stage3\AbstractNode {
        $attributes = self::parseAttributesNodes($node->attributes);

        $stage3Node = self::tryParseSlotDeclarationNode($node, [])
            ?? (self::tryParseComponentCallNode($node, [], $componentsMetadata)
            ?? new Stage3\PairedTagNode($node->name, $attributes, []));

        return self::applySpecialAttributes($stage3Node, $node->attributes);
    }

    /**
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function parsePairedTagNode(
        Stage2\PairedTagNode $node,
        array $componentsMetadata,
    ): Stage3\AbstractNode {
        $children = array_map(fn ($node) => self::parseStage2Node($node, $componentsMetadata), $node->children);
        $attributes = self::parseAttributesNodes($node->attributes);

        $stage3Node = self::tryParseFragmentNode($node, $children)
            ?? self::tryParseSlotDeclarationNode($node, $children)
            ?? self::tryParseComponentCallNode($node, $children, $componentsMetadata)
            ?? new Stage3\PairedTagNode($node->name, $attributes, $children);

        return self::applySpecialAttributes($stage3Node, $node->attributes);
    }

    /**
     * @param array<Stage2\DynamicAttributeNode|Stage2\StaticAttributeNode> $nodes
     * @return array<Stage3\DynamicAttributeNode|Stage3\StaticAttributeNode>
     */
    private static function parseAttributesNodes(array $nodes): array
    {
        /** @var array<Stage3\DynamicAttributeNode|Stage3\StaticAttributeNode> $attributesNodes */
        $attributesNodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof Stage2\DynamicAttributeNode) {
                if (!in_array($node->name, self::RESERVED_DYNAMIC_ATTRIBUTES, true)) {
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

        return $attributesNodes;
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
     * @param ComponentMetadata[] $componentsMetadata
     * @throws SapinException
     */
    private static function tryParseComponentCallNode(
        Stage2\PairedTagNode|Stage2\SelfClosedTagNode $node,
        array $children,
        array $componentsMetadata,
    ): ?Stage3\ComponentCallNode {
        if (($componentMetadata = self::tryGetComponentMetadataByName($node->name, $componentsMetadata)) === null) {
            return null;
        }

        $attributes = self::parseAttributesNodes($node->attributes);

        $props = [];

        foreach ($attributes as $key => $attribute) {
            foreach ($componentMetadata->properties as $property) {
                if ($attribute->name === $property->name) {
                    $props[] = match ($attribute::class) {
                        Stage3\StaticAttributeNode::class => new Stage3\StaticComponentPropertyNode(
                            name: $attribute->name,
                            children: $attribute->children,
                            type: $property->type,
                        ),
                        Stage3\DynamicAttributeNode::class => new Stage3\DynamicComponentPropertyNode(
                            name: $attribute->name,
                            expression: $attribute->expression,
                            type: $attribute->expression,
                        ),
                        default => throw new SapinException(
                            sprintf('Unexpected node "%s"', $node::class),
                        ),
                    };

                    unset($attributes[$key]);
                    break;
                }
            }
        }

        return new Stage3\ComponentCallNode(
            $componentMetadata->classFqn,
            $props,
            array_values($attributes),
            $children,
        );
    }

    /** @param array<Stage2\DynamicAttributeNode|Stage2\StaticAttributeNode> $attributes */
    private static function applySpecialAttributes(
        Stage3\AbstractNode $node,
        array $attributes,
    ): Stage3\AbstractNode {
        for ($i = count($attributes) - 1; $i >= 0; --$i) {
            $attribute = $attributes[$i];
            if (!($attribute instanceof Stage2\DynamicAttributeNode)) {
                continue;
            }

            $node = match ($attribute->name) {
                'if' => new Stage3\IfNode($attribute->expression, $node),
                'else-if' => new Stage3\ElseIfNode($attribute->expression, $node),
                'else' => new Stage3\ElseNode($node),
                'foreach' => new Stage3\ForEachNode($attribute->expression, $node),
                'for' => new Stage3\ForNode($attribute->expression, $node),
                'slot' => new Stage3\SlotContentNode($attribute->expression, $node),
                default => null,
            } ?? $node;
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
