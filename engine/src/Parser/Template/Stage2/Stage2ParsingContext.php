<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2;

use Sapin\Engine\Parser\Template\Stage2\Node as Stage2;
use function array_key_exists;
use function count;

final class Stage2ParsingContext
{
    public Stage2\PairedTagNode $current;

    /**
     * @param Stage2\PairedTagNode[] $stack
     * @param array<int, null> $closedTagsSet
     */
    public function __construct(
        public readonly Stage2\PairedTagNode $root = new Stage2\PairedTagNode('root', [], []),
        ?Stage2\PairedTagNode $current = null,
        private array $stack = [],
        private array $closedTagsSet = [],
    ) {
        $this->current = $current ?? $this->root;
    }

    public function setPairedTagClosed(Stage2\PairedTagNode $node): void
    {
        $this->closedTagsSet[spl_object_id($node)] = null;
    }

    public function isPairedTagClosed(Stage2\PairedTagNode $node): bool
    {
        return array_key_exists(spl_object_id($node), $this->closedTagsSet);
    }

    public function pushStack(Stage2\PairedTagNode $node): void
    {
        $this->stack[] = $node;
    }

    public function popStack(): void
    {
        $this->current = array_pop($this->stack) ?? $this->root;
    }

    public function pushCurrentChildren(Stage2\AbstractNode $node): void
    {
        $this->current->children[] = $node;
    }

    public function popCurrentChildren(): ?Stage2\AbstractNode
    {
        return array_pop($this->current->children);
    }

    public function isStackEmpty(): bool
    {
        return count($this->stack) === 0;
    }
}
