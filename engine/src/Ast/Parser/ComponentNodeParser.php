<?php

namespace Sapin\Engine\Ast\Parser;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Engine\Ast\Node\ComponentNode;
use Sapin\Engine\Ast\Node\ScopedStyleNode;
use Sapin\Engine\Ast\Node\StyleNode;
use Sapin\Engine\Ast\Node\Template\TemplateNode;
use Sapin\Engine\SapinException;

final class ComponentNodeParser
{
    /**
     * @throws SapinException
     */
    public function parse(string $componentFilePath): ComponentNode
    {
        $componentFileContents = file_get_contents($componentFilePath)
            ?: throw new SapinException(sprintf('Failed to read contents of "%s"', $componentFilePath));

        $componentFile = PhpFile::fromCode($componentFileContents);

        $componentClass = $this->getComponentClass($componentFile)
            ?? throw new SapinException(sprintf('No component class found in "%s"', $componentFilePath));

        $scopeId = uniqid();

        return new ComponentNode(
            file: $componentFile,
            class: $componentClass,
            templateNode: $this->tryParseTemplateNode($componentClass, $componentFileContents, $scopeId, ),
            styleNodes: $this->parseStyleNodes($componentFileContents, $scopeId),
        );
    }

    private function getComponentClass(PhpFile $file): ?ClassType
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
     */
    private function tryParseTemplateNode(
        ClassType $componentClass,
        string $componentFileContents,
        string $scopeId,
    ): ?TemplateNode {
        $templateStart = strpos($componentFileContents, '<template');
        $templateEnd = strpos($componentFileContents, '</template>');

        if ($templateStart === false) {
            return null;
        }

        if ($templateEnd === false) {
            throw new SapinException(sprintf(
                'Unterminated <template> node in %s component',
                $componentClass->getName()
            ));
        }

        return (new TemplateNodeParser())->parse(substr(
            $componentFileContents,
            $templateStart,
            $templateEnd + strlen('</template>') - $templateStart,
        ), $scopeId);
    }

    /**
     * @return StyleNode[]
     */
    private function parseStyleNodes(string $componentFileContents, string $scopeId): array
    {
        preg_match_all(
            '/<style(\s+scoped)?\s*?>([\s\S]*)<\/style>/U',
            $componentFileContents,
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(function (array $styleMatch) use ($scopeId) {
            return $styleMatch[1] !== ''
                ? new ScopedStyleNode($styleMatch[2], $scopeId)
                : new StyleNode($styleMatch[2]);
        }, $matches);
    }
}
