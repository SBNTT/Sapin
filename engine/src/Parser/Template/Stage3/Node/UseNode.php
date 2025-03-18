<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class UseNode
{
    public function __construct(
        public readonly string $componentName,
        public readonly string $classFqn,
    ) {}
}
