<?php

namespace Sapin\Ast\Parser;

use DOMNode;
use Exception;
use Masterminds\HTML5\Parser\DOMTreeBuilder;
use Masterminds\HTML5\Parser\Scanner;
use Masterminds\HTML5\Parser\Tokenizer;
use Sapin\Ast\Node\Template\TemplateNode;

final readonly class TemplateNodeParser
{
    /**
     * @throws Exception
     */
    public function parse(string $html): TemplateNode
    {
        $templateDomNode = $this->parseHtmlTemplate($html);

        $usesAttribute = $templateDomNode->attributes?->getNamedItem(':uses');

        /** @var string[] $uses */
        $uses = $usesAttribute !== null
            ? preg_split('/\s*,\s*/', trim($usesAttribute->nodeValue ?? '', ", \n\r\t\v\0"))
            : [];

        $templateNode = new TemplateNode();

        foreach ($uses as $use) {
            if (!class_exists($use)) {
                throw new Exception(sprintf('Could not found class "%s"', $use));
            }

            $className = substr(
                strrchr($use, "\\") ?: throw new Exception(sprintf('Invalid use: "%s"', $use)),
                1,
            );

            $templateNode->addUse($className, $use);
        }

        $templateNode->addChildren(
            (new TemplateElementNodeParser())->parseChildren($templateDomNode, $templateNode),
        );

        return $templateNode;
    }

    private function parseHtmlTemplate(string $html): DOMNode
    {
        // Default options from HTML5 class
        $options = [
            // Whether the serializer should aggressively encode all characters as entities.
            'encode_entities' => false,

            // Prevents the parser from automatically assigning the HTML5 namespace to the DOM document.
            'disable_html_ns' => false,
        ];

        $events = new DOMTreeBuilder(false, $options);
        $scanner = new Scanner($html, 'UTF-8');
        $parser = new Tokenizer($scanner, $events, Tokenizer::CONFORMANT_XML);

        $parser->parse();

        // $errors = $events->getErrors();

        return $events->document()->getElementsByTagName('template')[0];
    }
}
