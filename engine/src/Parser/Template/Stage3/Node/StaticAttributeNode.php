<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

/**
 * @extends AbstractCompositeNode<RawNode|InterpolationNode>
 */
class StaticAttributeNode extends AbstractCompositeNode
{
    /** @param array<RawNode|InterpolationNode> $children */
    public function __construct(
        public readonly string $name,
        array $children,
        public readonly string $delimiter,
    ) {
        parent::__construct($children);
    }
}
