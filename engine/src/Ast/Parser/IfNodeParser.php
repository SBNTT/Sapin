<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\IfNode;

final class IfNodeParser
{
    public function parse(string $expression, AbstractNode $child): IfNode
    {
        $ifNode = new IfNode($expression);
        $ifNode->addChild($child);

        return $ifNode;
    }
}
