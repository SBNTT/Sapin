<?php

namespace Sapin\Engine\Ast\Parser;

use DOMAttr;
use DOMComment;
use DOMElement;
use DOMNode;
use DOMText;
use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\FragmentNode;
use Sapin\Engine\Ast\Node\Template\HtmlTagNode;
use Sapin\Engine\Ast\Node\Template\HtmlTagStaticAttributeNode;
use Sapin\Engine\Ast\Node\Template\SlotContentNode;
use Sapin\Engine\Ast\Node\Template\TemplateElementNode;
use Sapin\Engine\Ast\Node\Template\TemplateNode;
use Sapin\Engine\Ast\Node\Template\TextNode;
use Sapin\Engine\SapinException;

final class TemplateElementNodeParser
{
    /**
     * @throws SapinException
     */
    public function tryParse(DOMNode $domNode, TemplateNode $templateNode, string $scopeId): ?TemplateElementNode
    {
        if ($domNode instanceof DOMComment) {
            return null;
        } elseif ($domNode instanceof DOMText) {
            if (($value = $domNode->nodeValue) === null) {
                return null;
            }

            return new TextNode(
                content: trim($value),
            );
        } elseif ($domNode instanceof DOMElement) {
            $elementNode =
                   (new FragmentNodeParser())->tryParse($domNode)
                ?? (new SlotDeclarationNodeParser())->tryParse($domNode)
                ?? (new ComponentCallNodeParser())->tryParse($domNode, $templateNode)
                ?? (new HtmlTagNodeParser())->parse($domNode);

            $elementNode->addChildren($this->parseChildren($domNode, $templateNode, $scopeId));

            if (($slotAttributeValue = $domNode->attributes->getNamedItem(':slot')?->nodeValue) !== null) {
                if ($elementNode instanceof HtmlTagNode) {
                    $elementNode->addAttribute(new HtmlTagStaticAttributeNode(
                        name: 'data-scope',
                        value: new TextNode($scopeId),
                    ));
                } elseif ($elementNode instanceof FragmentNode) {
                    $subElementsNodes = ParsingHelper::findFirstDescendantsNodesOfType($elementNode, HtmlTagNode::class);
                    foreach ($subElementsNodes as $subElementNode) {
                        $subElementNode->addAttribute(new HtmlTagStaticAttributeNode(
                            name: 'data-scope',
                            value: new TextNode($scopeId),
                        ));
                    }
                }

                return new SlotContentNode(
                    name: $slotAttributeValue,
                    child: $elementNode,
                );
            }

            /** @var DOMAttr[] $attributes */
            $attributes = array_reverse(iterator_to_array($domNode->attributes->getIterator()));
            foreach ($attributes as $attribute) {
                $elementNode = match ($attribute->name) {
                    ':if' => (new IfNodeParser())->parse($attribute->nodeValue ?? '', $elementNode),
                    ':else-if' => (new ElseIfNodeParser())->parse($attribute->nodeValue ?? '', $elementNode),
                    ':else' => (new ElseNodeParser())->parse($elementNode),
                    ':foreach' => (new ForEachNodeParser())->parse($attribute->nodeValue ?? '', $elementNode),
                    ':for' => (new ForNodeParser())->parse($attribute->nodeValue ?? '', $elementNode),
                    default => null,
                } ?? $elementNode;
            }

            return $elementNode;
        }

        throw new SapinException(sprintf('Unsupported DOMNode "%s"', get_class($domNode)));
    }

    /**
     * @return TemplateElementNode[]
     * @throws SapinException
     */
    public function parseChildren(DOMNode $domNode, TemplateNode $templateNode, string $scopeId): array
    {
        return array_filter(
            array_map(
                fn (DOMNode $childNode) => $this->tryParse($childNode, $templateNode, $scopeId),
                iterator_to_array($domNode->childNodes),
            ),
            fn (?AbstractNode $node) => $node !== null && (!($node instanceof TextNode) || !$node->isEmpty()),
        );
    }
}
