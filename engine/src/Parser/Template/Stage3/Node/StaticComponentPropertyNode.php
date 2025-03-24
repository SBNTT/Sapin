<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class StaticComponentPropertyNode extends StaticAttributeNode
{
    /** @param array<RawNode|InterpolationNode> $children */
    public function __construct(
        string $name,
        array $children,
        public string $type,
    ) {
        parent::__construct($name, $children, '');
    }
}
