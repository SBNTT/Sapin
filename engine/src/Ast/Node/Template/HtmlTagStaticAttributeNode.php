<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

final class HtmlTagStaticAttributeNode extends HtmlTagAttributeNode
{
    public function __construct(
        private readonly string $name,
        private readonly TextNode $value,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write($this->name)
            ->write('="')
            ->compileNode($this->value)
            ->write('"');
    }
}
