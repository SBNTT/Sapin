<?php

namespace Sapin\Engine\Ast;

use Sapin\Engine\Ast\Node\AbstractNode;

final class Compiler
{
    private string $out = '';
    private int $indentations = 0;
    private bool $isCurrentLineBlank = true;

    public function indent(): self
    {
        $this->indentations++;

        return $this;
    }

    public function dedent(): self
    {
        $this->indentations--;

        return $this;
    }

    public function writeLn(string $content): self
    {
        $this->write($content);
        $this->out .= PHP_EOL;
        $this->isCurrentLineBlank = true;

        return $this;
    }

    public function write(string $content): self
    {
        if ($this->isCurrentLineBlank) {
            $this->out .= str_repeat(' ', $this->indentations * 4);
        }

        $this->out .= $content;
        $this->isCurrentLineBlank = false;

        return $this;
    }

    public function writePhpOpeningTag(): self
    {
        return $this->write('<?php ');
    }

    public function writePhpClosingTag(): self
    {
        return $this->write('?>');
    }

    public function compileNode(AbstractNode $node): self
    {
        $node->compile($this);

        return $this;
    }

    /** @param AbstractNode[] $nodes */
    public function compileNodes(array $nodes): self
    {
        foreach ($nodes as $node) {
            $node->compile($this);
        }

        return $this;
    }

    public function getOut(): string
    {
        return $this->out;
    }
}
