<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

final class ComponentMetadata
{
    /** @param ComponentProperty[] $properties */
    public function __construct(
        public readonly string $name,
        public readonly string $classFqn,
        public readonly array $properties,
    ) {}
}
