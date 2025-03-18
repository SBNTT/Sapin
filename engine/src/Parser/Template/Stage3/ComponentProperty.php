<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

final class ComponentProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
    ) {}
}
