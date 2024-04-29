<?php

namespace Sapin\Ast\Parser;

use DOMAttr;
use DOMComment;
use DOMElement;
use DOMNode;
use DOMText;
use Sapin\Ast\Node\AbstractNode;
use Sapin\Ast\Node\Template\SlotContentNode;
use Sapin\Ast\Node\Template\TemplateElementNode;
use Sapin\Ast\Node\Template\TemplateNode;
use Sapin\Ast\Node\Template\TextNode;

final readonly class TemplateElementNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(DOMNode $domNode, TemplateNode $viewNode): ?TemplateElementNode
    {
        if ($domNode instanceof DOMComment) {
            return null;
        } elseif ($domNode instanceof DOMText) {
            return new TextNode(
                content: trim($domNode->nodeValue ?? ''),
            );
        } elseif ($domNode instanceof DOMElement) {
            $elementNode = (new HtmlTagNodeParser())->parse($domNode, $viewNode);

            $elementNode->addChildren($this->parseChildren($domNode, $viewNode));

            if (($slotAttributeValue = $domNode->attributes->getNamedItem(':slot')?->nodeValue) !== null) {
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

        throw new \Exception(sprintf('Unsupported DOMNode "%s"', get_class($domNode)));
    }

    /**
     * @return AbstractNode[]
     * @throws \Exception
     */
    public function parseChildren(DOMNode $domNode, TemplateNode $viewNode): array
    {
        return array_filter(
            array_map(
                fn (DOMNode $childNode) => $this->parse($childNode, $viewNode),
                iterator_to_array($domNode->childNodes),
            ),
            fn (?AbstractNode $node) => $node !== null && (!($node instanceof TextNode) || !$node->isEmpty()),
        );
    }
}
