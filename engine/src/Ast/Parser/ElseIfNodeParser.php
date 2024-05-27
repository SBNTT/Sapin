<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\ElseIfNode;

final class ElseIfNodeParser
{
    public function parse(string $expression, AbstractNode $child): ElseIfNode
    {
        $elseIfNode = new ElseIfNode($expression);
        $elseIfNode->addChild($child);
        return $elseIfNode;
    }
}
