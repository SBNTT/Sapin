<?php

declare(strict_types=1);

namespace Sapin\Engine\Attribute;

use Attribute;
use Sapin\Engine\Parser\Template\Stage3\ComponentLoaderMetadata;

#[Attribute(Attribute::TARGET_CLASS)]
final class ComponentLoader
{
    /** @param class-string<ComponentLoaderMetadata> $classFqn */
    public function __construct(
        public readonly string $classFqn,
    ) {}
}
