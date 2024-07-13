<?php

namespace Sapin\Engine;

final class RenderingContext
{
    /** @var class-string[] */
    public array $renderedComponentStyles = [];

    public function __construct(
        public readonly bool $shouldRenderStyles = true,
    ) {
    }
}
