<?php

namespace Sapin\Engine\Ast\Parser;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Engine\Ast\Node\ComponentNode;
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

        $componentNode = new ComponentNode(
            file: $componentFile,
            class: $componentClass,
        );

        if (($templateNode = $this->tryParseTemplateNode($componentClass, $componentFileContents)) !== null) {
            $componentNode->addChild($templateNode);
        }

        return $componentNode;
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
    private function tryParseTemplateNode(ClassType $componentClass, string $componentFileContents): ?TemplateNode
    {
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
        ));
    }
}
