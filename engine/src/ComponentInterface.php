<?php

declare(strict_types=1);

namespace Sapin\Engine;

interface ComponentInterface
{
    /** @param ?callable(string $name, callable(): void $default): void $slotRenderer */
    public function render(RenderingContext $context, ?callable $slotRenderer = null): void;
}
