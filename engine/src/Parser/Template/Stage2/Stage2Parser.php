<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2;

use Sapin\Engine\Parser\Template\Stage1\Node as Stage1;
use Sapin\Engine\Parser\Template\Stage2\Node as Stage2;

abstract class Stage2Parser
{
    /**
     * @param Stage1\AbstractNode[] $nodes
     * @return Stage2\AbstractNode[]
     */
    public static function parseStage1Nodes(array $nodes): array
    {
        $context = new Stage2ParsingContext();

        foreach ($nodes as $node) {
            match ($node::class) {
                Stage1\OpeningTagNode::class => self::handleOpeningTagNode($context, $node),
                Stage1\ClosingTagNode::class => self::handleClosingTagNode($context, $node),
                Stage1\InterpolationNode::class => self::handleInterpolationTagNode($context, $node),
                Stage1\RawNode::class => self::handleRawNode($context, $node),
                Stage1\CommentNode::class => self::handleCommentNode($context, $node),
                Stage1\SelfClosedTagNode::class => self::handleSelfClosedTagNode($context, $node),
                default => null,
            };
        }

        self::handleRemainingStackNodes($context);

        return $context->root->children;
    }

    private static function handleOpeningTagNode(
        Stage2ParsingContext $context,
        Stage1\OpeningTagNode $node,
    ): void {
        $newNode = new Stage2\PairedTagNode(
            name: $node->name,
            attributes: self::convertStage1AttributesToStage2Ones($node->attributes),
            children: [],
        );

        $context->pushCurrentChildren($newNode);
        $context->pushStack($context->current);
        $context->current = $newNode;
    }

    private static function handleClosingTagNode(
        Stage2ParsingContext $context,
        Stage1\ClosingTagNode $node,
    ): void {
        while ($context->current->name !== $node->name) {
            $orphanOpeningNode = $context->current;
            $context->popStack();
            $context->popCurrentChildren();

            self::pushPairedTagNodeAsInlineOne($context, $orphanOpeningNode);
        }

        $context->setPairedTagClosed($context->current);
        $context->popStack();
    }

    private static function handleInterpolationTagNode(
        Stage2ParsingContext $context,
        Stage1\InterpolationNode $node,
    ): void {
        $context->pushCurrentChildren(new Stage2\InterpolationNode(
            expression: $node->expression,
        ));
    }

    private static function handleRawNode(
        Stage2ParsingContext $context,
        Stage1\RawNode $node,
    ): void {
        $context->pushCurrentChildren(new Stage2\RawNode(
            content: $node->content,
        ));
    }

    private static function handleCommentNode(
        Stage2ParsingContext $context,
        Stage1\CommentNode $node,
    ): void {
        $context->pushCurrentChildren(new Stage2\CommentNode(
            content: $node->content,
        ));
    }

    private static function handleSelfClosedTagNode(
        Stage2ParsingContext $context,
        Stage1\SelfClosedTagNode $node,
    ): void {
        $context->pushCurrentChildren(new Stage2\SelfClosedTagNode(
            name: $node->name,
            attributes: self::convertStage1AttributesToStage2Ones($node->attributes),
        ));
    }

    private static function handleRemainingStackNodes(Stage2ParsingContext $context): void
    {
        while (!$context->isStackEmpty()) {
            $context->popStack();
            $lastNode = $context->popCurrentChildren();

            if ($lastNode instanceof Stage2\PairedTagNode && !$context->isPairedTagClosed($lastNode)) {
                self::pushPairedTagNodeAsInlineOne($context, $lastNode);
            } elseif ($lastNode !== null) {
                $context->pushCurrentChildren($lastNode);
            }
        }
    }

    /**
     * @param array<Stage1\DynamicAttributeNode|Stage1\StaticAttributeNode> $attributes
     * @return array<Stage2\DynamicAttributeNode|Stage2\StaticAttributeNode>
     */
    private static function convertStage1AttributesToStage2Ones(array $attributes): array
    {
        return array_map(
            fn ($attribute) => match ($attribute::class) {
                Stage1\DynamicAttributeNode::class => new Stage2\DynamicAttributeNode(
                    name: $attribute->name,
                    expression: $attribute->expression,
                    delimiter: $attribute->delimiter,
                ),
                Stage1\StaticAttributeNode::class => new Stage2\StaticAttributeNode(
                    name: $attribute->name,
                    children: self::convertStage1AttributeChildrenToStage2Ones($attribute->children),
                    delimiter: $attribute->delimiter,
                ),
            },
            $attributes,
        );
    }

    /**
     * @param array<Stage1\RawNode|Stage1\InterpolationNode> $nodes
     * @return array<Stage2\RawNode|Stage2\InterpolationNode>
     */
    private static function convertStage1AttributeChildrenToStage2Ones(array $nodes): array
    {
        return array_map(
            fn ($node) => match ($node::class) {
                Stage1\RawNode::class => new Stage2\RawNode($node->content),
                Stage1\InterpolationNode::class => new Stage2\InterpolationNode($node->expression),
            },
            $nodes,
        );
    }

    private static function pushPairedTagNodeAsInlineOne(
        Stage2ParsingContext $context,
        Stage2\PairedTagNode $node,
    ): void {
        $context->pushCurrentChildren(new Stage2\InlineTagNode($node->name, $node->attributes));

        foreach ($node->children as $nodeChild) {
            $context->pushCurrentChildren($nodeChild);
        }
    }
}
