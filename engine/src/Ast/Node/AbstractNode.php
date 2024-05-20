<?php

namespace Sapin\Ast\Node;

use Sapin\Ast\Compiler;

abstract class AbstractNode
{
    /** @var AbstractNode[] */
    protected array $children;

    protected ?AbstractNode $parent;

    public function __construct()
    {
        $this->children = [];
        $this->parent = null;
    }

    /**
     * @param AbstractNode[] $children
     */
    public function addChildren(array $children): void
    {
        array_walk($children, [$this, 'addChild']);
    }

    public function addChild(AbstractNode $child): void
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    public function getNextSibling(): ?AbstractNode
    {
        $parentChildren = $this->parent?->children;
        if ($parentChildren === null || count($parentChildren) < 2) {
            return null;
        }

        for ($i = 0; $i < count($parentChildren); $i++) {
            if ($parentChildren[$i] === $this) {
                return $parentChildren[$i + 1] ?? null;
            }
        }

        return null;
    }

    public function getPreviousSibling(): ?AbstractNode
    {
        $parentChildren = $this->parent?->children;
        if ($parentChildren === null || count($parentChildren) < 2) {
            return null;
        }

        $previousSibling = $parentChildren[0];
        for ($i = 1; $i < count($parentChildren); $i++) {
            if ($parentChildren[$i] === $this) {
                return $previousSibling;
            }

            $previousSibling = $parentChildren[$i];
        }

        return null;
    }

    abstract public function compile(Compiler $compiler): void;

    /**
     * @return AbstractNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getParent(): ?AbstractNode
    {
        return $this->parent;
    }
}
