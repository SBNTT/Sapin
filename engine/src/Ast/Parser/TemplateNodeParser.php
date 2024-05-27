<?php

namespace Sapin\Engine\Ast\Parser;

use DOMNode;
use Masterminds\HTML5\Parser\DOMTreeBuilder;
use Masterminds\HTML5\Parser\Scanner;
use Masterminds\HTML5\Parser\Tokenizer;
use Sapin\Engine\Ast\Node\Template\TemplateNode;
use Sapin\Engine\SapinException;

final class TemplateNodeParser
{
    /**
     * @throws SapinException
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
            /** @var string[] $useParts */
            $useParts = preg_split('/\s+as\s+/', $use);

            $fqn = $useParts[0];
            $alias = $useParts[1] ?? null;

            $templateNode->addUse(
                $alias ?? basename(str_replace('\\', '/', $fqn)),
                $fqn,
            );
        }

        $templateNode->addChildren(
            (new TemplateElementNodeParser())->parseChildren($templateDomNode, $templateNode),
        );

        return $templateNode;
    }

    /**
     * @throws SapinException
     */
    private function parseHtmlTemplate(string $html): DOMNode
    {
        try {
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
        } catch (\Masterminds\HTML5\Exception $e) {
            throw new SapinException('Failed to parse HTML template', previous: $e);
        }
    }
}
