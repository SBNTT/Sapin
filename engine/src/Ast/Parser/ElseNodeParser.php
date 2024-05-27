<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\ElseNode;

final class ElseNodeParser
{
    public function parse(AbstractNode $child): ElseNode
    {
        $elseNode = new ElseNode();
        $elseNode->addChild($child);
        return $elseNode;
    }
}
