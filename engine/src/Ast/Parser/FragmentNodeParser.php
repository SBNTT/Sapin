<?php

namespace Sapin\Engine\Ast\Parser;

use DOMNode;
use Sapin\Engine\Ast\Node\Template\FragmentNode;

final class FragmentNodeParser
{
    public function tryParse(DOMNode $domNode): ?FragmentNode
    {
        return $domNode->nodeName === 'fragment'
            ? new FragmentNode()
            : null;
    }
}
