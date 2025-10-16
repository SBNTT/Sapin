<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use ReflectionParameter;

final class ComponentLoaderMetadata
{
    /** @param ReflectionParameter[] $parameters */
    public function __construct(
        public readonly string $classFqn,
        public readonly array $parameters,
    ) {}
}
