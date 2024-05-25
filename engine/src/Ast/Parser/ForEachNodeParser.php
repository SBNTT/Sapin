<?php

namespace Sapin\Ast\Parser;

use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\ForEachNode;

final class ForEachNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(string $expression, AbstractNode $child): ForEachNode
    {
        $forEachNode = new ForEachNode($expression);
        $forEachNode->addChild($child);
        return $forEachNode;
    }
}
