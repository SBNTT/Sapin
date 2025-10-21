<?php

declare(strict_types=1);

namespace Sapin\Engine\Compiler;

use Closure;
use Generator;
use Sapin\Engine\Parser\Component\Node\ComponentNode;
use Sapin\Engine\Renderable;

abstract class ComponentCompiler
{
    public static function compileComponentNode(ComponentNode $node, SourceCodeBuffer $buffer): void
    {
        $templateBuffer = new SourceCodeBuffer();
        TemplateCompiler::compileStage3Nodes($node->templateNodes, $templateBuffer);

        $node->class->addImplement(Renderable::class);

        $renderTemplateMethod = $node->class->addMethod('render');
        $renderTemplateMethod->addComment('@inheritdoc ');
        $renderTemplateMethod->addParameter('slotRenderer')
            ->setType(Closure::class)
            ->setNullable()
            ->setDefaultValue(null);
        $renderTemplateMethod->setReturnType(Generator::class);
        $renderTemplateMethod->setBody($templateBuffer->getOut());

        $buffer->write((string) $node->file);
    }
}
