<?php

namespace Sapin\Ast\Node;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Ast\Compiler;
use Sapin\ComponentInterface;

final class ComponentNode extends AbstractNode
{
    public function __construct(
        private readonly PhpFile   $file,
        private readonly ClassType $class,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $childrenCompiler = new Compiler();
        $childrenCompiler->compileNodes($this->children);

        // $this->class->setName('_' . $this->class->getName());
        $this->class->addImplement(ComponentInterface::class);

        $renderMethod = $this->class->addMethod('render');
        $renderMethod->addComment('@inheritdoc ');
        $renderMethod->addParameter('slotRenderer')
            ->setType('callable')
            ->setNullable()
            ->setDefaultValue(null);
        $renderMethod->setReturnType('void');
        $renderMethod->setBody('?>' . $childrenCompiler->getOut() . '<?php');

        $compiler->write((string)$this->file);
    }
}
