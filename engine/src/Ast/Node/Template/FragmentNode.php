<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

final class FragmentNode extends TemplateElementNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler->compileNodes($this->children);
    }
}
