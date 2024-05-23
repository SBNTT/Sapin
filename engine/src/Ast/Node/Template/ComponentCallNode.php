<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

final class ComponentCallNode extends TemplateElementNode
{
    /**
     * @param array<string, string> $props
     */
    public function __construct(
        protected readonly string $componentFqn,
        protected readonly array $props,
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
        foreach ($this->children as $childNode) {
            if (!($childNode instanceof SlotContentNode)) {
                continue;
            }

            $slotCompiler = new Compiler();
            $childNode->compile($slotCompiler);
            $slots[$childNode->name] = $slotCompiler->getOut();
        }

        $compiler
            ->writePhpOpeningTag()
            ->write('\Sapin\Sapin::render(')
            ->write('new \\' . $this->componentFqn . '(')
            ->write(implode(',', $compiledProps))
            ->write(')');

        if (count($slots) > 0) {
            $compiler
                ->write(',')
                ->write('function(string $slot,callable $default){');

            $if = false;
            foreach ($slots as $slotName => $slotValue) {
                $compiler
                    ->write($if ? 'else if' : 'if')
                    ->write("(\$slot==='" . $slotName . "'){")
                    ->writePhpClosingTag()
                    ->write($slotValue)
                    ->writePhpOpeningTag()
                    ->write('}');

                $if = true;
            }

            $compiler
                ->write('else{$default();}')
                ->write('}');
        }

        $compiler
            ->write(');')
            ->writePhpClosingTag();
    }
}
