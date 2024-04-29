<?php

namespace Sapin\Ast\Parser;

use Exception;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Ast\Node\ComponentNode;

final readonly class ComponentNodeParser
{
    /**
     * @throws Exception
     */
    public function parse(string $componentClassCode): ComponentNode
    {
        $componentFile = PhpFile::fromCode($componentClassCode);
        $componentClass = $this->getComponentClass($componentFile) ?? throw new Exception('Component class not found');

        $componentNode = new ComponentNode(
            file: $componentFile,
            class: $componentClass,
        );

        $templateStart = strpos($componentClassCode, '<template')
            ?: throw new Exception(sprintf(
                'Could not find <template> opening tag for "%s" class',
                $componentClass->getName()
            ));

        $templateEnd = strpos($componentClassCode, '</template>')
            ?: throw new Exception(sprintf(
                'Could not find <template> closing tag for "%s" class',
                $componentClass->getName()
            ));

        $template = trim(substr($componentClassCode, $templateStart, $templateEnd + strlen('</template>') - $templateStart), "\n\r");
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
