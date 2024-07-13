<?php

namespace Sapin\Engine\Ast\Parser;

use DOMAttr;
use DOMNode;
use Sapin\Engine\Ast\Node\Template\HtmlTagDynamicAttributeNode;
use Sapin\Engine\Ast\Node\Template\HtmlTagNode;
use Sapin\Engine\Ast\Node\Template\HtmlTagStaticAttributeNode;
use Sapin\Engine\Ast\Node\Template\TextNode;

use function strtolower;

final class HtmlTagNodeParser
{
    public function parse(DOMNode $domNode): HtmlTagNode
    {
        $attributes = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (str_starts_with($attribute->name, ':')) {
                $attributeName = strtolower(substr($attribute->name, 1));
                if (!in_array($attributeName, TemplateNodeParser::RESERVED_DYNAMIC_ATTRIBUTES)) {
                    $attributes[] = new HtmlTagDynamicAttributeNode($attributeName, $attribute->value);
                }
            } else {
                $attributes[] = new HtmlTagStaticAttributeNode($attribute->name, new TextNode($attribute->value));
            }
        }

        return new HtmlTagNode(strtolower($domNode->nodeName), $attributes);
    }
}
