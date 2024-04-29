<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

class ElseIfNode extends TemplateElementNode
{
    public function __construct(
        private readonly string $expression,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write('elseif(' . $this->expression . '){')
            ->writePhpClosingTag()
            ->compileNodes($this->children)
            ->writePhpOpeningTag()
            ->write('}');

        $nextSiblingNode = $this->getNextSibling();
        if (!($nextSiblingNode instanceof ElseIfNode || $nextSiblingNode instanceof ElseNode)) {
            $compiler->writePhpClosingTag();
        }
    }
}
