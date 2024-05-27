<?php

namespace Sapin\Engine;

interface ComponentInterface
{
    /**
     * @param ?callable(string $name, callable(): void $default): void $slotRenderer
     */
    public function render(?callable $slotRenderer = null): void;
}
