<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class SlotDeclarationNode extends TemplateElementNode
{
    public function __construct(
        private readonly string $name,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->writePhpOpeningTag()
            ->write('$defaultSlotRenderer = function(){')
            ->writePhpClosingTag()
            ->compileNodes($this->children)
            ->writePhpOpeningTag()
            ->write('};')
            ->write('$slotRenderer === null')
            ->write(' ? $defaultSlotRenderer()')
            ->write(" : \$slotRenderer('" . $this->name . "', \$defaultSlotRenderer);")
            ->writePhpClosingTag();
    }
}
