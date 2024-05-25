<?php

namespace Sapin\Ast\Parser;

use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\ElseIfNode;

final class ElseIfNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(string $expression, AbstractNode $child): ElseIfNode
    {
        $elseIfNode = new ElseIfNode($expression);
        $elseIfNode->addChild($child);
        return $elseIfNode;
    }
}
