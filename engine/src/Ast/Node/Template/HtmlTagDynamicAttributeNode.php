<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

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
            ->write('\Sapin\Sapin::echo(' . $this->expression . ');')
            ->writePhpClosingTag()
            ->write('"');
    }
}
