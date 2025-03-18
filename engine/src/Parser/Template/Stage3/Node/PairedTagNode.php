<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class PairedTagNode extends AbstractCompositeNode
{
    /**
     * @param array<DynamicAttributeNode|StaticAttributeNode> $attributes
     * @param AbstractNode[] $children
     */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
        array $children,
    ) {
        parent::__construct($children);
    }
}
