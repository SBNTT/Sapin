<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;

final class HtmlTagNode extends TemplateElementNode
{
    /**
     * @param HtmlTagAttributeNode[] $attributes
     */
    public function __construct(
        private readonly string $name,
        private array $attributes,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->write('<' . strtolower($this->name));

        foreach ($this->attributes as $attribute) {
            $compiler
                ->write(' ')
                ->compileNode($attribute);
        }

        $compiler
            ->write('>')
            ->compileNodes($this->children)
            ->write('</' . strtolower($this->name) . '>');
    }

    public function addAttribute(HtmlTagAttributeNode $attributeNode): void
    {
        $this->attributes[] = $attributeNode;
    }
}
