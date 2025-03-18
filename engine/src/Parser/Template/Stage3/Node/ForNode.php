<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3\Node;

final class ForNode extends AbstractWrapperNode
{
    public function __construct(
        public readonly string $expression,
        AbstractNode $child,
    ) {
        parent::__construct($child);
    }
}
