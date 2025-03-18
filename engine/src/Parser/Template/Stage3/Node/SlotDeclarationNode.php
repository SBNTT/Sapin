<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class SlotDeclarationNode extends AbstractCompositeNode
{
    /** @param AbstractNode[] $children */
    public function __construct(
        public readonly string $name,
        array $children,
    ) {
        parent::__construct($children);
    }
}
