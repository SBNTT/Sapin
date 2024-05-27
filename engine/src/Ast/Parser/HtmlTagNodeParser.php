<?php

namespace Sapin\Ast\Parser;

use DOMAttr;
use DOMNode;
use Sapin\Ast\Node\Template\ComponentCallNode;
use Sapin\Ast\Node\Template\FragmentNode;
use Sapin\Ast\Node\Template\HtmlTagDynamicAttributeNode;
use Sapin\Ast\Node\Template\HtmlTagNode;
use Sapin\Ast\Node\Template\HtmlTagStaticAttributeNode;
use Sapin\Ast\Node\Template\SlotDeclarationNode;
use Sapin\Ast\Node\Template\TemplateNode;
use Sapin\Ast\Node\Template\TextNode;
use Sapin\SapinException;

final class HtmlTagNodeParser
{
    private const RESERVED_DYNAMIC_ATTRIBUTES = [
        'foreach', 'for', 'if', 'else-if', 'else', 'slot'
    ];

    /**
     * @throws SapinException
     */
    public function parse(DOMNode $domNode, TemplateNode $templateNode): HtmlTagNode|ComponentCallNode|FragmentNode|SlotDeclarationNode
    {
        if ($domNode->nodeName === 'fragment') {
            return new FragmentNode();
        }

        if ($domNode->nodeName === 'slot') {
            $slotName = $domNode->attributes?->getNamedItem(':name')?->nodeValue
                ?? throw new SapinException('Missing ":name attribute on slot element');

            return new SlotDeclarationNode($slotName);
        }

        return ($componentFqn = $templateNode->getUse($domNode->nodeName)) !== null
            ? $this->parseComponentCallNode($domNode, $componentFqn)
            : $this->parseHtmlTagNode($domNode);
    }

    private function parseHtmlTagNode(DOMNode $domNode): HtmlTagNode
    {
        $attributes = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (str_starts_with($attribute->name, ':')) {
                $attributeName = substr($attribute->name, 1);
                if (!in_array($attributeName, self::RESERVED_DYNAMIC_ATTRIBUTES)) {
                    $attributes[] = new HtmlTagDynamicAttributeNode($attributeName, $attribute->value);
                }
            } else {
                $attributes[] = new HtmlTagStaticAttributeNode($attribute->name, new TextNode($attribute->value));
            }
        }

        return new HtmlTagNode($domNode->nodeName, $attributes);
    }

    private function parseComponentCallNode(DOMNode $domNode, string $componentFqn): ComponentCallNode
    {
        /** @var array<string, string> $props */
        $props = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (str_starts_with($attribute->name, ':')) {
                $attributeName = substr($attribute->name, 1);
                if (!in_array($attributeName, self::RESERVED_DYNAMIC_ATTRIBUTES)) {
                    $props[$attributeName] = $attribute->value;
                }
            }
        }

        return new ComponentCallNode($componentFqn, $props);
    }
}
