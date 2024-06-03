<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\ForEachNode;

final class ForEachNodeParser
{
    public function parse(string $expression, AbstractNode $child): ForEachNode
    {
        $forEachNode = new ForEachNode($expression);
        $forEachNode->addChild($child);

        return $forEachNode;
    }
}
