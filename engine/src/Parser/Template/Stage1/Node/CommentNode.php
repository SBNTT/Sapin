<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage1\Node;

final class CommentNode extends AbstractNode
{
    public function __construct(
        public readonly string $content,
    ) {}
}
