<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class InlineTagNode extends AbstractNode
{
    /** @param array<DynamicAttributeNode|StaticAttributeNode> $attributes */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
    ) {}
}
