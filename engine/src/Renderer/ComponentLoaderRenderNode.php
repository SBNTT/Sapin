<?php

declare(strict_types=1);

namespace Sapin\Engine\Renderer;

use Closure;
use Generator;
use Sapin\Engine\Component;
use Sapin\Engine\ComponentLoaderInterface;
use Stringable;

final class ComponentLoaderRenderNode
{
    public bool $preLoaded = false;

    /**
     * @param ComponentLoaderInterface<Component> $loader
     * @param ?Closure(string): (Generator<string|int|float|bool|Stringable|ComponentRenderNode|ComponentLoaderRenderNode>|false) $slotRenderer
     */
    public function __construct(
        public readonly ComponentLoaderInterface $loader,
        public readonly ?Closure $slotRenderer = null,
    ) {}
}
