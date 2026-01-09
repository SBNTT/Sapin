<?php

declare(strict_types=1);

namespace Sapin\Engine;

use Closure;
use Generator;
use Sapin\Engine\Renderer\ComponentLoaderRenderNode;
use Sapin\Engine\Renderer\ComponentRenderNode;
use Stringable;

interface Renderable
{
    /**
     * @param ?Closure(string): (Generator<string|int|float|bool|Stringable|ComponentRenderNode|ComponentLoaderRenderNode>|false) $slotRenderer
     * @return Generator<string|int|float|bool|Stringable|ComponentRenderNode|ComponentLoaderRenderNode>
     */
    public function render(?Closure $slotRenderer = null): Generator;
}
