<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class DynamicComponentPropertyNode extends DynamicAttributeNode
{
    public function __construct(
        string $name,
        string $expression,
        public string $type,
    ) {
        parent::__construct($name, $expression);
    }
}
