<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

final class ForNode extends TemplateElementNode
{
    public function __construct(
        private readonly string $expression
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->writePhpOpeningTag()
            ->write('for(' . $this->expression . '){')
            ->writePhpClosingTag()
            ->compileNodes($this->children)
            ->writePhpOpeningTag()
            ->write('}')
            ->writePhpClosingTag();
    }
}
