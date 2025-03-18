<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Component;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Engine\Parser\Component\Node\ComponentNode;
use Sapin\Engine\Parser\Template\Stage1\Stage1Parser;
use Sapin\Engine\Parser\Template\Stage2\Stage2Parser;
use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;
use Sapin\Engine\Parser\Template\Stage3\Stage3Parser;
use Sapin\Engine\SapinException;
use function sprintf;
use function strlen;

abstract class ComponentParser
{
    /** @throws SapinException */
    public static function parseFile(string $componentFilePath): ComponentNode
    {
        $componentFileContents = file_get_contents($componentFilePath)
            ?: throw new SapinException(sprintf('Failed to read contents of "%s"', $componentFilePath));

        $componentFile = PhpFile::fromCode($componentFileContents);

        $componentClass = self::getComponentClass($componentFile)
            ?? throw new SapinException(sprintf('No component class found in "%s"', $componentFilePath));

        return new ComponentNode(
            file: $componentFile,
            class: $componentClass,
            templateNodes: self::tryParseTemplateNodes($componentClass, $componentFileContents),
        );
    }

    private static function getComponentClass(PhpFile $file): ?ClassType
    {
        foreach ($file->getClasses() as $class) {
            if ($class instanceof ClassType) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @throws SapinException
     * @return Stage3\AbstractNode[]
     */
    private static function tryParseTemplateNodes(ClassType $componentClass, string $componentFileContents): array
    {
        $templateStart = strpos($componentFileContents, '<template');
        $templateEnd = strpos($componentFileContents, '</template>');

        if ($templateStart === false) {
            return [];
        }

        if ($templateEnd === false) {
            throw new SapinException(sprintf(
                'Unterminated <template> node in %s component',
                $componentClass->getName(),
            ));
        }

        $template = substr(
            $componentFileContents,
            $templateStart,
            $templateEnd + strlen('</template>') - $templateStart,
        );

        $nodes = Stage1Parser::parseString($template);
        $nodes = Stage2Parser::parseStage1Nodes($nodes);

        return Stage3Parser::parseStage2Nodes($nodes);
    }
}
