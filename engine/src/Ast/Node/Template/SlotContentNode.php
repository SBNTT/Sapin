<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

class SlotContentNode extends TemplateElementNode
{
    public function __construct(
        public readonly string $name,
        private readonly TemplateElementNode $child
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->compileNode($this->child);
    }
}
