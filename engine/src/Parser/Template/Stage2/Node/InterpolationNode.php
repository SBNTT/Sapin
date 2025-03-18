<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2\Node;

final class InterpolationNode extends AbstractNode
{
    public function __construct(
        public readonly string $expression,
    ) {}
}
