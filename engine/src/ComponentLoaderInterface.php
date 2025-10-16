<?php

declare(strict_types=1);

namespace Sapin\Engine;

/**
 * @template T of ComponentInterface
 */
interface ComponentLoaderInterface
{
    /** @return T */
    public function load(): mixed;
}
