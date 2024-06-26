<?php

namespace Sapin\Engine\Ast\Node;

use Sapin\Engine\Ast\Compiler;
use Sapin\Engine\Ast\Node\Template\TemplateElementNode;

class StyleNode extends TemplateElementNode
{
    public function __construct(
        protected readonly string $content,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write('<style>')
            ->write($this->content)
            ->write('</style>');
    }
}
