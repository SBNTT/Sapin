<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\SlotContentNode;
use Sapin\Engine\Ast\Node\Template\TemplateElementNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class SlotContentNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $childNode = $this->createMock(TemplateElementNode::class);
        $node = new SlotContentNode('slotName', $childNode);

        $compiler->expects(self::once())
            ->method('compileNode')
            ->with($childNode);

        $node->compile($compiler);

        self::assertSame('[child]', $compiler->getOut());
    }
}
