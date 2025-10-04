<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

use Sapin\Engine\Parser\Template\Stage3\SpecialAttribute;

class SpecialAttributeNode extends AbstractNode
{
    public function __construct(
        public readonly SpecialAttribute $kind,
        public readonly string $expression,
        public readonly string $delimiter,
    ) {}
}
