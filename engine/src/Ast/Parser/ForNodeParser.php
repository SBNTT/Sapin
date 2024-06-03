<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\ForNode;

final class ForNodeParser
{
    public function parse(string $expression, AbstractNode $child): ForNode
    {
        $forNode = new ForNode($expression);
        $forNode->addChild($child);

        return $forNode;
    }
}
