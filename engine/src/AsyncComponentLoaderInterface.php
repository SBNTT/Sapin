<?php

declare(strict_types=1);

namespace Sapin\Engine;

/**
 * @template T of ComponentInterface
 * @extends ComponentLoaderInterface<T>
 */
interface AsyncComponentLoaderInterface extends ComponentLoaderInterface
{
    public function preLoad(): void;
}
