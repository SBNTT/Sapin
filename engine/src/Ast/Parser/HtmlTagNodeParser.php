<?php

namespace Sapin\Ast\Parser;

use DOMAttr;
use DOMNode;
use Sapin\Ast\Node\Template\ComponentCallNode;
use Sapin\Ast\Node\Template\FragmentNode;
use Sapin\Ast\Node\Template\HtmlTagNode;
use Sapin\Ast\Node\Template\SlotDeclarationNode;
use Sapin\Ast\Node\Template\TemplateNode;
use Sapin\Ast\Node\Template\TextNode;

final readonly class HtmlTagNodeParser
{
    private const array RESERVED_DYNAMIC_ATTRIBUTES = [
        'foreach', 'for', 'if', 'else-if', 'else', 'slot'
    ];

    /**
     * @throws \Exception
     */
    public function parse(DOMNode $domNode, TemplateNode $templateNode): HtmlTagNode|ComponentCallNode|FragmentNode|SlotDeclarationNode
    {
        if ($domNode->nodeName === 'fragment') {
            return new FragmentNode();
        }

        if ($domNode->nodeName === 'slot') {
            $slotName = $domNode->attributes?->getNamedItem(':name')?->nodeValue
                ?? throw new \Exception('Missing ":name attribute on slot element');

            return new SlotDeclarationNode($slotName);
        }

        /** @var array<string, TextNode> $staticAttributes */
        $staticAttributes = [];

        /** @var array<string, string> $dynamicAttributes */
        $dynamicAttributes = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (str_starts_with($attribute->name, ':')) {
                $attributeName = substr($attribute->name, 1);
                if (!in_array($attributeName, self::RESERVED_DYNAMIC_ATTRIBUTES)) {
                    $dynamicAttributes[$attributeName] = $attribute->value;
                }
            } else {
                $staticAttributes[$attribute->name] = new TextNode($attribute->value);
            }
        }

        return ($componentFqn = $templateNode->getUse($domNode->nodeName)) !== null
            ? new ComponentCallNode(
                componentFqn: $componentFqn,
                props: $dynamicAttributes,
            ) : new HtmlTagNode(
                name: strtolower($domNode->nodeName),
                staticAttributes: $staticAttributes,
                dynamicAttributes: $dynamicAttributes,
            );
    }
}
