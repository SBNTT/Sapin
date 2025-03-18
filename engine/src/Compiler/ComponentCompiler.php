<?php

declare(strict_types=1);

namespace Sapin\Engine\Compiler;

use Sapin\Engine\ComponentInterface;
use Sapin\Engine\Parser\Component\Node\ComponentNode;
use Sapin\Engine\RenderingContext;

abstract class ComponentCompiler
{
    public static function compileComponentNode(ComponentNode $node, SourceCodeBuffer $buffer): void
    {
        $templateBuffer = new SourceCodeBuffer();
        TemplateCompiler::compileStage3Nodes($node->templateNodes, $templateBuffer);

        $node->class->addImplement(ComponentInterface::class);

        $renderTemplateMethod = $node->class->addMethod('render');
        $renderTemplateMethod->addComment('@inheritdoc ');
        $renderTemplateMethod->addParameter('context')
            ->setType(RenderingContext::class);
        $renderTemplateMethod->addParameter('slotRenderer')
            ->setType('callable')
            ->setNullable()
            ->setDefaultValue(null);
        $renderTemplateMethod->setReturnType('void');
        $renderTemplateMethod->setBody('?>' . $templateBuffer->getOut() . '<?php');

        $buffer->write((string) $node->file);
    }
}
