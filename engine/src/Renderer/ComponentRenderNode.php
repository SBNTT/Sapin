<?php

declare(strict_types=1);

namespace Sapin\Engine\Renderer;

use Closure;
use Generator;
use Sapin\Engine\Component;
use Sapin\Engine\ComponentLoaderInterface;
use Stringable;

final class ComponentRenderNode
{
    public bool $preLoaded = false;

    /**
     * @param Component|ComponentLoaderInterface<Component> $component
     * @param ?Closure(string): (Generator<string|int|float|bool|Stringable|ComponentRenderNode>|false) $slotRenderer
     */
    public function __construct(
        public readonly Component|ComponentLoaderInterface $component,
        public readonly ?Closure $slotRenderer = null,
    ) {}
}
