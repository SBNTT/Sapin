<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

use JsonSerializable;
use ReflectionClass;
use ReflectionException;

abstract class AbstractNode implements JsonSerializable
{
    public ?AbstractNode $parent = null;

    /**
     * @throws ReflectionException
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $properties = get_object_vars($this);
        if ($this->parent !== null) {
            $properties['parent'] = (new ReflectionClass($this->parent))->getShortName();
        } else {
            unset($properties['parent']);
        }

        return [
            '__type' => (new ReflectionClass($this))->getShortName(),
            ...$properties,
        ];
    }
}
