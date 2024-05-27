<?php

namespace Sapin\Ast\Parser;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Ast\Node\ComponentNode;
use Sapin\SapinException;

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

        $templateStart = strpos($componentFileContents, '<template')
            ?: throw new SapinException(sprintf(
                'Could not find <template> opening tag for "%s" component template',
                $componentClass->getName()
            ));

        $templateEnd = strpos($componentFileContents, '</template>')
            ?: throw new SapinException(sprintf(
                'Could not find <template> closing tag for "%s" component template',
                $componentClass->getName()
            ));

        $template = trim(substr($componentFileContents, $templateStart, $templateEnd + strlen('</template>') - $templateStart), "\n\r");
        $componentNode->addChild((new TemplateNodeParser())->parse($template));

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
}
