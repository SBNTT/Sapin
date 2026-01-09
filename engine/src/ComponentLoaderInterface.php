<?php

declare(strict_types=1);

namespace Sapin\Engine;

/**
 * @template-covariant T of Component
 */
interface ComponentLoaderInterface
{
    /** @return T */
    public function load(): mixed;
}
