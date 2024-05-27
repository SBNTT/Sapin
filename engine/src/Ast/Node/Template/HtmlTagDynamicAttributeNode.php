<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class HtmlTagDynamicAttributeNode extends HtmlTagAttributeNode
{
    public function __construct(
        private readonly string $name,
        private readonly string $expression,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write($this->name)
            ->write('="')
            ->writePhpOpeningTag()
            ->write('\\Sapin\\Engine\\Sapin::echo(' . $this->expression . ');')
            ->writePhpClosingTag()
            ->write('"');
    }
}
