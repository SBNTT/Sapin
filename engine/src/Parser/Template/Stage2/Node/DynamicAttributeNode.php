<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2\Node;

final class DynamicAttributeNode extends AbstractNode
{
    public function __construct(
        public readonly string $name,
        public readonly string $expression,
        public readonly string $delimiter,
    ) {}
}
