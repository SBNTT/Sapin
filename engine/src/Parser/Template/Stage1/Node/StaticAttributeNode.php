<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage1\Node;

final class StaticAttributeNode extends AbstractNode
{
    /** @param array<RawNode|InterpolationNode> $children */
    public function __construct(
        public readonly string $name,
        public readonly array $children,
        public readonly string $delimiter,
    ) {}
}
