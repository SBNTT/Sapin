<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage1;

use Sapin\Engine\Parser\Template\Stage1\Node\AbstractNode;
use Sapin\Engine\Parser\Template\Stage1\Node\ClosingTagNode;
use Sapin\Engine\Parser\Template\Stage1\Node\CommentNode;
use Sapin\Engine\Parser\Template\Stage1\Node\DynamicAttributeNode;
use Sapin\Engine\Parser\Template\Stage1\Node\InterpolationNode;
use Sapin\Engine\Parser\Template\Stage1\Node\OpeningTagNode;
use Sapin\Engine\Parser\Template\Stage1\Node\RawNode;
use Sapin\Engine\Parser\Template\Stage1\Node\SelfClosedTagNode;
use Sapin\Engine\Parser\Template\Stage1\Node\StaticAttributeNode;
use function count;
use const PREG_SET_ORDER;

abstract class Stage1Parser
{
    private const REGEX = <<<REGEXP
            /
                  <!-- \s* (?<comment>.*?) \s* -->

                | <
                    (?<opening_tag_name>[^\s\/>]+)
                    \s*
                    (?<tag_attributes>(?:\s*[^\s=>\/"]+(?:="[^"]*")?)+)?
                    \s*
                    (?<closing_char>\/)?
                    \s*
                  >

                | <\/
                    (?<closing_tag_name>[^\s\/]+)
                    \s*
                  >

                | {{ (?<interpolation>[^}]*) }}

                | (?<raw>
                    (?:\S\s?) | (?: (?<=\}\})\s(?!\s*\}\}) | \s(?=\}\}) )
                  )
            /xm
        REGEXP;

    private const ATTRIBUTES_REGEX = '/(?<attribute_name>[^\s="]+)(?:="(?<attribute_value>(?:[^"\\\\]|\\\\.)*)")?/m';

    private const ATTRIBUTE_VALUE_REGEX = '/(?<raw>[^{}]*)?(?:{{\s*(?<interpolation>[^}\s]*)\s*}})?/m';

    /** @return AbstractNode[] */
    public static function parseString(string $content): array
    {
        preg_match_all(self::REGEX, $content, $matches, PREG_SET_ORDER);

        /** @var AbstractNode[] $nodes */
        $nodes = [];

        foreach ($matches as $match) {
            if (($name = $match['opening_tag_name'] ?? '') !== '') {
                $attributes = self::parseAttributes($match['tag_attributes'] ?? '');
                $nodes[] = ($match['closing_char'] ?? '') !== ''
                    ? new SelfClosedTagNode($name, $attributes)
                    : new OpeningTagNode($name, $attributes);
            } elseif (($name = $match['closing_tag_name'] ?? '') !== '') {
                $nodes[] = new ClosingTagNode($name);
            } elseif (($content = $match['interpolation'] ?? '') !== '') {
                $nodes[] = new InterpolationNode(trim($content));
            } elseif (($content = $match['comment'] ?? '') !== '') {
                $nodes[] = new CommentNode(trim($content));
            } elseif (($rawContent = $match['raw'] ?? '') !== '') {
                /** @var ?RawNode $previous */
                $previous = count($nodes) > 0 && $nodes[count($nodes) - 1] instanceof RawNode
                    ? array_pop($nodes)
                    : null;

                $nodes[] = new RawNode(($previous?->content ?? '') . trim($rawContent, "\n\r\t\v\0"));
            }
        }

        return $nodes;
    }

    /** @return array<DynamicAttributeNode|StaticAttributeNode> */
    private static function parseAttributes(string $attributesMatch): array
    {
        preg_match_all(
            self::ATTRIBUTES_REGEX,
            $attributesMatch,
            $attributesMatches,
            PREG_SET_ORDER,
        );

        return array_map(
            function ($attributeMatch) {
                $name = $attributeMatch['attribute_name'];
                if (str_starts_with($name, ':')) {
                    return new DynamicAttributeNode(
                        name: substr($name, 1),
                        expression: $attributeMatch['attribute_value'] ?? '',
                    );
                }

                preg_match_all(
                    self::ATTRIBUTE_VALUE_REGEX,
                    $attributeMatch['attribute_value'] ?? '',
                    $attributeValueMatches,
                    PREG_SET_ORDER,
                );

                /** @var array<RawNode|InterpolationNode> $nodes */
                $nodes = [];
                foreach ($attributeValueMatches as $valueMatch) {
                    if (($rawContent = $valueMatch['raw'] ?? '') !== '') {
                        $nodes[] = new RawNode($rawContent);
                    }

                    if (($interpolationContent = $valueMatch['interpolation'] ?? '') !== '') {
                        $nodes[] = new InterpolationNode(trim($interpolationContent));
                    }
                }

                return new StaticAttributeNode(
                    name: $name,
                    children: $nodes,
                );
            },
            $attributesMatches,
        );
    }
}
