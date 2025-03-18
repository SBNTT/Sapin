<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

class DynamicAttributeNode extends AbstractNode
{
    public function __construct(
        public readonly string $name,
        public readonly string $expression,
    ) {}
}
