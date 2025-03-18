<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

/**
 * @template T of AbstractNode = AbstractNode
 */
abstract class AbstractCompositeNode extends AbstractNode
{
    /** @param T[] $children */
    public function __construct(
        public readonly array $children,
    ) {
        foreach ($this->children as $child) {
            $child->parent = $this;
        }
    }
}
