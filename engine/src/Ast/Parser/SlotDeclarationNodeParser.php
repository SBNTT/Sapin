<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\Template\SlotDeclarationNode;

final class SlotDeclarationNodeParser
{
    public function tryParse(\DOMNode $domNode): ?SlotDeclarationNode
    {
        if ($domNode->nodeName !== 'slot'
            || ($slotName = $domNode->attributes?->getNamedItem(':name')?->nodeValue) === null
        ) {
            return null;
        }

        return new SlotDeclarationNode($slotName);
    }
}
