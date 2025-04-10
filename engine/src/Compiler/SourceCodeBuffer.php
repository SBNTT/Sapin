<?php

declare(strict_types=1);

namespace Sapin\Engine\Compiler;

use Closure;
use function sprintf;
use const PHP_EOL;

final class SourceCodeBuffer
{
    private string $out = '';

    private int $indentations = 0;

    private bool $isCurrentLineBlank = true;

    /**
     * @template R
     * @param Closure(): R $cb
     */
    public function subCompile(Closure $cb): self
    {
        $cb();

        return $this;
    }

    /**
     * @template T
     * @template R
     * @param T[] $items
     * @param Closure(T): R $cb
     */
    public function subCompileEach(array $items, Closure $cb): self
    {
        foreach ($items as $item) {
            $cb($item);
        }

        return $this;
    }

    public function indent(): self
    {
        ++$this->indentations;

        return $this;
    }

    public function dedent(): self
    {
        --$this->indentations;

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

    public function writef(string $format, string|int|float ...$values): self
    {
        $this->write(sprintf($format, ...$values));

        return $this;
    }

    public function writefLn(string $format, string|int|float ...$values): self
    {
        $this->writeLn(sprintf($format, ...$values));

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

    public function getOut(): string
    {
        return $this->out;
    }
}
