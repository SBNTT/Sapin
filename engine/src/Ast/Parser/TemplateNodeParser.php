<?php

namespace Sapin\Ast\Parser;

use DOMDocument;
use Masterminds\HTML5;
use Sapin\Ast\Node\Template\TemplateNode;

final readonly class TemplateNodeParser
{
    /**
     * @throws \Exception
     */
    public function parse(string $html): TemplateNode
    {
        $html5 = new HTML5();
        $document = $html5->loadHTML($html);

        $templateNode = new TemplateNode();

        /** @var \DOMNode $templateDomNode */
        $templateDomNode = $document->getElementsByTagName('template')[0];

        $usesAttribute = $templateDomNode->attributes?->getNamedItem(':uses');

        /** @var string[] $uses */
        $uses = $usesAttribute !== null
            ? preg_split('/\s*,\s*/', trim($usesAttribute->nodeValue ?? '', ", \n\r\t\v\0"))
            : [];

        foreach ($uses as $use) {
            if (!class_exists($use)) {
                throw new \Exception(sprintf('Could not found class "%s"', $use));
            }

            $className = substr(
                strrchr($use, "\\") ?: throw new \Exception(sprintf('Invalid use: "%s"', $use)),
                1,
            );

            $templateNode->addUse($className, $use);
        }

        $templateNode->addChildren(
            (new TemplateElementNodeParser())->parseChildren($templateDomNode, $templateNode),
        );

        return $templateNode;
    }
}
