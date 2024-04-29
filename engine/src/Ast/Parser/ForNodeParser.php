<?php

namespace Sapin\Ast\Parser;

use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\ForNode;

final readonly class ForNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(string $expression, AbstractNode $child): ForNode
    {
        $forNode = new ForNode($expression);
        $forNode->addChild($child);
        return $forNode;
    }
}
