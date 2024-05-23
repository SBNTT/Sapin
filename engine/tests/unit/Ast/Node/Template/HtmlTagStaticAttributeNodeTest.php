<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\HtmlTagStaticAttributeNode;
use Sapin\Ast\Node\Template\TextNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class HtmlTagStaticAttributeNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);

        /** @noinspection PhpUnitInvalidMockingEntityInspection */
        $mockValue = $this->createMock(TextNode::class);

        $node = new HtmlTagStaticAttributeNode('name', $mockValue);

        $compiler->expects(self::once())
            ->method('compileNode')
            ->with($mockValue);

        $node->compile($compiler);

        self::assertSame(
            'name="[child]"',
            $compiler->getOut(),
        );
    }
}
