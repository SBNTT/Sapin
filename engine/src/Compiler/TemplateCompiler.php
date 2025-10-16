<?php

declare(strict_types=1);

namespace Sapin\Engine\Compiler;

use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;
use function array_key_exists;
use function count;
use function sprintf;

abstract class TemplateCompiler
{
    /** @param Stage3\AbstractNode[] $nodes */
    public static function compileStage3Nodes(array $nodes, SourceCodeBuffer $buffer): void
    {
        $rootNode = new class($nodes) extends Stage3\AbstractCompositeNode {};

        self::compileNodes($rootNode->children, $buffer);
    }

    /** @param Stage3\AbstractNode[] $nodes */
    private static function compileNodes(array $nodes, SourceCodeBuffer $buffer): void
    {
        foreach ($nodes as $node) {
            self::compileNode($node, $buffer);
        }
    }

    private static function compileNode(Stage3\AbstractNode $node, SourceCodeBuffer $buffer): void
    {
        match ($node::class) {
            Stage3\PairedTagNode::class => self::compilePairedTagNode($node, $buffer),
            Stage3\InlineTagNode::class => self::compileInlineTagNode($node, $buffer),
            Stage3\RawNode::class => self::compileRawNode($node, $buffer),
            Stage3\InterpolationNode::class => self::compileInterpolationNode($node, $buffer),
            Stage3\FragmentNode::class => self::compileFragmentNode($node, $buffer),
            Stage3\ForEachNode::class => self::compileForEachNode($node, $buffer),
            Stage3\ForNode::class => self::compileForNode($node, $buffer),
            Stage3\IfNode::class => self::compileIfNode($node, $buffer),
            Stage3\ElseIfNode::class => self::compileElseIfNode($node, $buffer),
            Stage3\ElseNode::class => self::compileElseNode($node, $buffer),
            Stage3\DynamicAttributeNode::class => self::compileDynamicAttributeNode($node, $buffer),
            Stage3\StaticAttributeNode::class => self::compileStaticAttributeNode($node, $buffer),
            Stage3\DynamicComponentPropertyNode::class => self::compileDynamicComponentPropertyNode($node, $buffer),
            Stage3\StaticComponentPropertyNode::class => self::compileStaticComponentPropertyNode($node, $buffer),
            Stage3\SlotContentNode::class => self::compileSlotContentNode($node, $buffer),
            Stage3\SlotDeclarationNode::class => self::compileSlotDeclarationNode($node, $buffer),
            Stage3\ComponentCallNode::class => self::compileComponentCallNode($node, $buffer),
            default => null,
        };
    }

    private static function compileComponentCallNode(Stage3\ComponentCallNode $node, SourceCodeBuffer $buffer): void
    {
        /**
         * keys: slot name; values: children nodes
         * @var array<string, Stage3\AbstractNode[]> $slots
         */
        $slots = [];

        foreach ($node->children as $child) {
            $slotName = $child instanceof Stage3\SlotContentNode ? $child->name : 'children';
            if (!array_key_exists($slotName, $slots)) {
                $slots[$slotName] = [];
            }

            $slots[$slotName][] = $child;
        }

        $buffer
            ->writeLn('yield new \\Sapin\\Engine\\Renderer\\ComponentRenderNode(')
            ->indent();

        if (count($node->props) === 0) {
            $buffer
                ->writefLn('component: new \\%s(),', $node->classFqn);
        } else {
            $buffer
                ->writefLn('component: new \\%s(', $node->classFqn)
                ->indent()
                ->subCompileEach(
                    $node->props,
                    fn ($property) => $buffer
                        ->subCompile(fn () => self::compileNode($property, $buffer))
                        ->writeLn(','),
                )
                ->dedent()
                ->writeLn('),');
        }

        if (count($slots) > 0) {
            $buffer
                ->writeLn('slotRenderer: function(string $slot) {')
                ->indent();

            $i = 0;
            foreach ($slots as $slotName => $slotChildren) {
                $buffer
                    ->writefLn('%sif ($slot === \'%s\') {', $i > 0 ? 'else' : '', $slotName)
                    ->indent()
                    ->subCompile(fn () => self::compileNodes($slotChildren, $buffer))
                    ->dedent()
                    ->write('} ');

                ++$i;
            }

            $buffer
                ->writeLn('else {')
                ->indent()
                ->writeLn('return false;')
                ->dedent()
                ->writeLn('}')
                ->dedent()
                ->writeLn('},');
        }

        $buffer
            ->dedent()
            ->writeLn(');');
    }

    private static function compileDynamicComponentPropertyNode(
        Stage3\DynamicComponentPropertyNode $node,
        SourceCodeBuffer $buffer,
    ): void {
        $buffer
            ->write($node->name)
            ->write(': ')
            ->write($node->expression);
    }

    private static function compileStaticComponentPropertyNode(
        Stage3\StaticComponentPropertyNode $node,
        SourceCodeBuffer $buffer,
    ): void {
        $children = array_map(
            fn ($child) => match ($child::class) {
                Stage3\RawNode::class => match ($node->type) {
                    'string' => sprintf("'%s'", str_replace("'", "\'", $child->content)),
                    default => $child->content,
                },
                Stage3\InterpolationNode::class => $child->expression
            },
            $node->children,
        );

        $childrenSeparator = match ($node->type) {
            'string' => '.',
            default => ''
        };

        $buffer
            ->write($node->name)
            ->write(': ')
            ->write(implode($childrenSeparator, $children));
    }

    private static function compileSlotDeclarationNode(Stage3\SlotDeclarationNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefLn('if ($slotRenderer !== null && ($nodes = $slotRenderer(\'%s\')) !== false) {', $node->name)
            ->indent()
            ->writeLn('yield from $nodes;')
            ->dedent()
            ->write('}');

        if (count($node->children) === 0) {
            $buffer->writeLn('');
        } else {
            $buffer
                ->writeLn(' else {')
                ->indent()
                ->subCompile(fn () => self::compileNodes($node->children, $buffer))
                ->dedent()
                ->writeLn('}');
        }
    }

    private static function compileSlotContentNode(Stage3\SlotContentNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileNode($node->child, $buffer);
    }

    private static function compilePairedTagNode(Stage3\PairedTagNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileOpeningTagNode($buffer, $node->name, $node->attributes);

        $buffer
            ->indent()
            ->subCompile(fn () => self::compileNodes($node->children, $buffer))
            ->dedent()
            ->writefYieldStr('</%s>', true, $node->name);
    }

    private static function compileInlineTagNode(Stage3\InlineTagNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileOpeningTagNode($buffer, $node->name, $node->attributes);
    }

    /** @param array<Stage3\DynamicAttributeNode|Stage3\StaticAttributeNode> $attributes */
    private static function compileOpeningTagNode(SourceCodeBuffer $buffer, string $name, array $attributes): void
    {
        if (count($attributes) === 0) {
            $buffer->writefYieldStr('<%s>', true, $name);
        } else {
            $buffer
                ->writefYieldStr('<%s', true, $name)
                ->indent()
                ->subCompileEach(
                    $attributes,
                    fn ($attribute) => $buffer
                        ->writeYieldStr(' ', true)
                        ->subCompile(fn () => self::compileNode($attribute, $buffer)),
                )
                ->dedent()
                ->writeYieldStr('>', true);
        }
    }

    private static function compileRawNode(Stage3\RawNode $node, SourceCodeBuffer $buffer): void
    {
        if ($node->content === '') {
            return;
        }

        $buffer->writeYieldStr($node->content, quoteSafe: false);
    }

    private static function compileFragmentNode(Stage3\FragmentNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileNodes($node->children, $buffer);
    }

    private static function compileInterpolationNode(Stage3\InterpolationNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writeYieldExpr($node->expression);
    }

    private static function compileForEachNode(Stage3\ForEachNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefLn('foreach (%s) {', $node->expression)
            ->indent()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->dedent()
            ->writeLn('}');
    }

    private static function compileForNode(Stage3\ForNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefLn('for (%s) {', $node->expression)
            ->indent()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->dedent()
            ->writeLn('}');
    }

    private static function compileIfNode(Stage3\IfNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefLn('if (%s) {', $node->expression)
            ->indent()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->dedent()
            ->writeLn('}');
    }

    private static function compileElseIfNode(Stage3\ElseIfNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefLn('elseif (%s) {', $node->expression)
            ->indent()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->dedent()
            ->writeLn('}');
    }

    private static function compileElseNode(Stage3\ElseNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writeLn('else {')
            ->indent()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->dedent()
            ->writeLn('}');
    }

    private static function compileDynamicAttributeNode(Stage3\DynamicAttributeNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefYieldStr('%s=%s', true, $node->name, $node->delimiter)
            ->indent()
            ->writeYieldExpr($node->expression)
            ->dedent()
            ->writeYieldStr($node->delimiter, true);
    }

    private static function compileStaticAttributeNode(Stage3\StaticAttributeNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writefYieldStr('%s=%s', true, $node->name, $node->delimiter)
            ->indent()
            ->subCompile(fn () => self::compileNodes($node->children, $buffer))
            ->dedent()
            ->writeYieldStr($node->delimiter, true);
    }
}
