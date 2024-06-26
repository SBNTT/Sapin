<?php

namespace Sapin\Engine\Ast\Node;

use Sapin\Engine\Ast\Compiler;

final class ScopedStyleNode extends StyleNode
{
    public function __construct(
        string $content,
        private readonly string $scopeId,
    ) {
        parent::__construct($content);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write('<style>')
            ->write('@scope ([data-scope="')
            ->write($this->scopeId)
            ->write('"]) to ([data-scope])')
            ->write('{')
            ->write($this->content)
            ->write('}')
            ->write('</style>');
    }
}
