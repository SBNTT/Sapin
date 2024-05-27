<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class FragmentNode extends TemplateElementNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler->compileNodes($this->children);
    }
}
