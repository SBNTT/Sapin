<?php

declare(strict_types=1);

namespace Sapin\Engine\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ComponentLoader
{
    public function __construct(
        public readonly string $classFqn,
    ) {}
}
