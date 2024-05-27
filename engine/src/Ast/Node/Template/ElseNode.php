<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

class ElseNode extends TemplateElementNode
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write('else{')
            ->writePhpClosingTag()
            ->compileNodes($this->children)
            ->writePhpOpeningTag()
            ->write('}')
            ->writePhpClosingTag();
    }
}
