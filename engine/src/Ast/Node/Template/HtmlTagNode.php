<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

class HtmlTagNode extends TemplateElementNode
{
    /**
     * @param array<string, TextNode> $staticAttributes
     * @param array<string, string> $dynamicAttributes
     */
    public function __construct(
        protected readonly string $name,
        protected readonly array  $staticAttributes,
        protected readonly array  $dynamicAttributes,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->write('<' . $this->name);

        foreach ($this->dynamicAttributes as $attributeName => $attributeExpression) {
            $compiler
                ->write(' ' . $attributeName . '=' . '"')
                ->writePhpOpeningTag()
                ->write('echo ' . $attributeExpression . ';')
                ->writePhpClosingTag()
                ->write('"');
        }

        foreach ($this->staticAttributes as $attributeName => $attributeValue) {
            $compiler
                ->write(' ' . $attributeName . '="')
                ->compileNode($attributeValue)
                ->write('"');
        }

        $compiler
            ->write('>')
            ->compileNodes($this->children)
            ->write('</' . $this->name . '>');
    }
}
