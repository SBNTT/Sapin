<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class ComponentCallNode extends TemplateElementNode
{
    /**
     * @param array<string, string> $props
     */
    public function __construct(
        protected readonly string $componentFqn,
        protected readonly array  $props,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiledProps = [];
        foreach ($this->props as $attributeName => $attributeValue) {
            $compiledProps[] = $attributeName . ':' . $attributeValue;
        }

        /**
         * keys: slot name; values: compiled slot node
         * @var array<string, string> $slots
         */
        $slots = [];

        /** @var TemplateElementNode[] $childrenSlotNodes */
        $childrenSlotNodes = [];

        foreach ($this->children as $childNode) {
            if ($childNode instanceof SlotContentNode) {
                $slotCompiler = new Compiler();
                $childNode->compile($slotCompiler);
                $slots[$childNode->name] = $slotCompiler->getOut();
            } else {
                $childrenSlotNodes[] = $childNode;
            }
        }

        if (count($childrenSlotNodes) > 0) {
            $slotCompiler = new Compiler();
            $slotCompiler->compileNodes($childrenSlotNodes);
            $slots['children'] = $slotCompiler->getOut();
        }

        $compiler
            ->writePhpOpeningTag()
            ->write('\\Sapin\\Engine\\Sapin::render(')
            ->write('new \\' . $this->componentFqn . '(')
            ->write(implode(',', $compiledProps))
            ->write(')');

        if (count($slots) > 0) {
            $compiler
                ->write(',function(string $slot,callable $default){')
                ->write('switch($slot){');

            foreach ($slots as $slotName => $slotValue) {
                $compiler
                    ->write("case'" . $slotName . "':")
                    ->writePhpClosingTag()
                    ->write($slotValue)
                    ->writePhpOpeningTag()
                    ->write('break;');
            }

            $compiler
                ->write('default:$default();')
                ->write('}}');
        }

        $compiler
            ->write(');')
            ->writePhpClosingTag();
    }
}
