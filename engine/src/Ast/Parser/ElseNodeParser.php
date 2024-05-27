<?php

namespace Sapin\Ast\Parser;

use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\ElseNode;

final class ElseNodeParser
{
    public function parse(AbstractNode $child): ElseNode
    {
        $elseNode = new ElseNode();
        $elseNode->addChild($child);
        return $elseNode;
    }
}
