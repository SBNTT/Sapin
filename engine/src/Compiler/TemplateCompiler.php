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
            ->writePhpOpeningTag()
            ->write('\\Sapin\\Engine\\Sapin::render(')
            ->writef('new \\%s(', $node->classFqn)
            ->subCompileEach(
                $node->props,
                fn ($property) => $buffer
                ->subCompile(fn () => self::compileNode($property, $buffer))
                ->write(','),
            )
            ->write(')');

        if (count($slots)) {
            $buffer
                ->write(',function(string $slot,callable $default) use ($context){')
                ->write('switch($slot){');

            foreach ($slots as $slotName => $slotChildren) {
                $buffer
                    ->writef("case'%s':", $slotName)
                    ->writePhpClosingTag()
                    ->subCompile(fn () => self::compileNodes($slotChildren, $buffer))
                    ->writePhpOpeningTag()
                    ->write('break;');
            }

            $buffer
                ->write('default:$default();')
                ->write('}}');
        } else {
            $buffer->write(',null');
        }

        $buffer
            ->write(',$context')
            ->write(');')
            ->writePhpClosingTag();
    }

    private static function compileDynamicComponentPropertyNode(
        Stage3\DynamicComponentPropertyNode $node,
        SourceCodeBuffer $buffer,
    ): void {
        $buffer
            ->write($node->name)
            ->write(':')
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
            ->write(':')
            ->write(implode($childrenSeparator, $children));
    }

    private static function compileSlotDeclarationNode(Stage3\SlotDeclarationNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writePhpOpeningTag()
            ->write('$defaultSlotRenderer = function(){')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNodes($node->children, $buffer))
            ->writePhpOpeningTag()
            ->write('};')
            ->write('$slotRenderer === null')
            ->write(' ? $defaultSlotRenderer()')
            ->writef(' : $slotRenderer(\'%s\', $defaultSlotRenderer);', $node->name)
            ->writePhpClosingTag();
    }

    private static function compileSlotContentNode(Stage3\SlotContentNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileNode($node->child, $buffer);
    }

    private static function compilePairedTagNode(Stage3\PairedTagNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write('<')
            ->write($node->name)
            ->subCompileEach(
                $node->attributes,
                fn ($attribute) => $buffer
                ->write(' ')
                ->subCompile(fn () => self::compileNode($attribute, $buffer)),
            )
            ->write('>')
            ->subCompile(fn () => self::compileNodes($node->children, $buffer))
            ->write('</')
            ->write($node->name)
            ->write('>');
    }

    private static function compileInlineTagNode(Stage3\InlineTagNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write('<')
            ->write($node->name)
            ->subCompileEach(
                $node->attributes,
                fn ($attribute) => $buffer
                ->write(' ')
                ->subCompile(fn () => self::compileNode($attribute, $buffer)),
            )
            ->write('>');
    }

    private static function compileRawNode(Stage3\RawNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer->write($node->content);
    }

    private static function compileFragmentNode(Stage3\FragmentNode $node, SourceCodeBuffer $buffer): void
    {
        self::compileNodes($node->children, $buffer);
    }

    private static function compileInterpolationNode(Stage3\InterpolationNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writePhpOpeningTag()
            ->write('\\Sapin\\Engine\\Sapin::echo(')
            ->write($node->expression)
            ->write(');')
            ->writePhpClosingTag();
    }

    private static function compileForEachNode(Stage3\ForEachNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writePhpOpeningTag()
            ->write('foreach(')
            ->write($node->expression)
            ->write('){')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->writePhpOpeningTag()
            ->write('}')
            ->writePhpClosingTag();
    }

    private static function compileForNode(Stage3\ForNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writePhpOpeningTag()
            ->write('for(')
            ->write($node->expression)
            ->write('){')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->writePhpOpeningTag()
            ->write('}')
            ->writePhpClosingTag();
    }

    private static function compileIfNode(Stage3\IfNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->writePhpOpeningTag()
            ->write('if(')
            ->write($node->expression)
            ->write('){')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->writePhpOpeningTag()
            ->write('}');

        $nextSiblingNode = self::getNodeNextSibling($node);
        if (!($nextSiblingNode instanceof Stage3\ElseIfNode || $nextSiblingNode instanceof Stage3\ElseNode)) {
            $buffer->writePhpClosingTag();
        }
    }

    private static function compileElseIfNode(Stage3\ElseIfNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write('elseif(')
            ->write($node->expression)
            ->write('){')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->writePhpOpeningTag()
            ->write('}');

        $nextSiblingNode = self::getNodeNextSibling($node);
        if (!($nextSiblingNode instanceof Stage3\ElseIfNode || $nextSiblingNode instanceof Stage3\ElseNode)) {
            $buffer->writePhpClosingTag();
        }
    }

    private static function compileElseNode(Stage3\ElseNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write('else{')
            ->writePhpClosingTag()
            ->subCompile(fn () => self::compileNode($node->child, $buffer))
            ->writePhpOpeningTag()
            ->write('}')
            ->writePhpClosingTag();
    }

    private static function compileDynamicAttributeNode(Stage3\DynamicAttributeNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write($node->name)
            ->write('="')
            ->writePhpOpeningTag()
            ->write('\\Sapin\\Engine\\Sapin::echo(')
            ->write($node->expression)
            ->write(');')
            ->writePhpClosingTag()
            ->write('"');
    }

    private static function compileStaticAttributeNode(Stage3\StaticAttributeNode $node, SourceCodeBuffer $buffer): void
    {
        $buffer
            ->write($node->name)
            ->write('="')
            ->subCompile(fn () => self::compileNodes($node->children, $buffer))
            ->write('"');
    }

    private static function getNodeNextSibling(Stage3\AbstractNode $node): ?Stage3\AbstractNode
    {
        if (!($node->parent instanceof Stage3\AbstractCompositeNode)) {
            return null;
        }

        $parentChildren = $node->parent->children ?? [];
        foreach ($parentChildren as $i => $child) {
            if ($child === $node) {
                return $parentChildren[$i + 1] ?? null;
            }
        }

        return null;
    }
}
