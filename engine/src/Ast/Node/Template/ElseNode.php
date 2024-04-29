<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

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
