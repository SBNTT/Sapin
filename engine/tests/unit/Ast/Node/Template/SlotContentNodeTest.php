<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\SlotContentNode;
use Sapin\Ast\Node\Template\TemplateElementNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class SlotContentNodeTest extends TestCase
{
    use CompilerMockingHelper;

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = $this->createMockCompiler();
        $childNode = $this->createMock(TemplateElementNode::class);
        $node = new SlotContentNode('slotName', $childNode);

        $compiler->expects(self::once())
            ->method('compileNode')
            ->with($childNode);

        $node->compile($compiler);

        self::assertSame('[child]', $compiler->getOut());
    }
}
