<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2\Node;

final class PairedTagNode extends AbstractNode
{
    /**
     * @param array<DynamicAttributeNode|StaticAttributeNode> $attributes
     * @param AbstractNode[] $children
     */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
        public array $children,
    ) {}
}
