<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage1\Node;

final class SelfClosedTagNode extends AbstractNode
{
    /** @param array<DynamicAttributeNode|StaticAttributeNode> $attributes */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
    ) {}
}
