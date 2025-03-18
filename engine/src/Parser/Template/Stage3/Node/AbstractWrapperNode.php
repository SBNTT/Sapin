<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

abstract class AbstractWrapperNode extends AbstractNode
{
    public function __construct(
        public readonly AbstractNode $child,
    ) {
        $this->child->parent = $this;
    }
}
