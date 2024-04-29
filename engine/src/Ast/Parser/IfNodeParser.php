<?php

namespace Sapin\Ast\Parser;

use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\IfNode;

final readonly class IfNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(string $expression, AbstractNode $child): IfNode
    {
        $ifNode = new IfNode($expression);
        $ifNode->addChild($child);
        return $ifNode;
    }
}
