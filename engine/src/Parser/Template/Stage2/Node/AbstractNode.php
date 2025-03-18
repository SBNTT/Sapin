<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage2\Node;

use JsonSerializable;
use ReflectionClass;

abstract class AbstractNode implements JsonSerializable
{
    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            '__type' => (new ReflectionClass($this))->getShortName(),
            ...get_object_vars($this),
        ];
    }
}
