<?php

namespace Sapin\Engine\Ast\Parser;

use DOMAttr;
use DOMNode;
use Sapin\Engine\Ast\Node\Template\ComponentCallNode;
use Sapin\Engine\Ast\Node\Template\TemplateNode;

final class ComponentCallNodeParser
{
    public function tryParse(DOMNode $domNode, TemplateNode $templateNode): ?ComponentCallNode
    {
        $componentFqn = $templateNode->getUse($domNode->nodeName);
        if ($componentFqn === null) {
            return null;
        }

        /** @var array<string, string> $props */
        $props = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (str_starts_with($attribute->name, ':')) {
                $attributeName = substr($attribute->name, 1);
                if (!in_array($attributeName, TemplateNodeParser::RESERVED_DYNAMIC_ATTRIBUTES)) {
                    $props[$attributeName] = $attribute->value;
                }
            }
        }

        return new ComponentCallNode($componentFqn, $props);
    }
}
