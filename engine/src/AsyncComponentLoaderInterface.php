<?php

declare(strict_types=1);

namespace Sapin\Engine;

/**
 * @template-covariant T of Component
 * @extends ComponentLoaderInterface<T>
 */
interface AsyncComponentLoaderInterface extends ComponentLoaderInterface
{
    public function preLoad(): void;
}
