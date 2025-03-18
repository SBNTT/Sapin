<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class ComponentCallNode extends AbstractCompositeNode
{
    /**
     * @param array<DynamicComponentPropertyNode|StaticComponentPropertyNode> $props
     * @param array<DynamicAttributeNode|StaticAttributeNode> $attributes
     * @param AbstractNode[] $children
     */
    public function __construct(
        public readonly string $classFqn,
        public readonly array $props,
        public readonly array $attributes,
        array $children,
    ) {
        parent::__construct($children);
    }
}
