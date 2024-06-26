<?php

namespace Sapin\Engine\Ast\Node;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Engine\Ast\Compiler;
use Sapin\Engine\Ast\Node\Template\TemplateNode;
use Sapin\Engine\ComponentInterface;
use Sapin\Engine\RenderingContext;

final class ComponentNode extends AbstractNode
{
    /**
     * @param StyleNode[] $styleNodes
     */
    public function __construct(
        private readonly PhpFile   $file,
        private readonly ClassType $class,
        private readonly ?TemplateNode $templateNode,
        private readonly array $styleNodes,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $templateCompiler = new Compiler();
        if ($this->templateNode !== null) {
            $templateCompiler->compileNode($this->templateNode);
        }

        $stylesCompiler = new Compiler();
        $stylesCompiler->compileNodes($this->styleNodes);

        $this->class->addImplement(ComponentInterface::class);

        $renderTemplateMethod = $this->class->addMethod('render');
        $renderTemplateMethod->addComment('@inheritdoc ');
        $renderTemplateMethod->addParameter('context')
            ->setType(RenderingContext::class);
        $renderTemplateMethod->addParameter('slotRenderer')
            ->setType('callable')
            ->setNullable()
            ->setDefaultValue(null);
        $renderTemplateMethod->setReturnType('void');
        $renderTemplateMethod->setBody('?>' . $templateCompiler->getOut() . '<?php');

        $renderStylesMethod = $this->class->addMethod('renderStyles');
        $renderStylesMethod->addParameter('context')
            ->setType(RenderingContext::class);
        $renderStylesMethod->setReturnType('void');
        $renderStylesMethod->setBody('?>' . $stylesCompiler->getOut() . '<?php');

        $compiler->write((string)$this->file);
    }
}
