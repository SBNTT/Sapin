<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class SlotContentNode extends TemplateElementNode
{
    public function __construct(
        public readonly string               $name,
        private readonly TemplateElementNode $child
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->compileNode($this->child);
    }
}
