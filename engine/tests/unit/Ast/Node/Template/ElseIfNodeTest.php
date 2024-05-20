<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\ElseIfNode;
use Sapin\Ast\Node\Template\ElseNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class ElseIfNodeTest extends TestCase
{
    use CompilerMockingHelper;

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = $this->createMockCompiler();
        $node = new ElseIfNode('expression');

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame(
            'elseif(expression){?>[children]<?php }?>',
            $compiler->getOut(),
        );
    }

    // ---

    public static function nodesPreventingPhpTagClosureDataProvider(): array
    {
        return [
            [ElseIfNode::class],
            [ElseNode::class]
        ];
    }

    #[Test, DataProvider('nodesPreventingPhpTagClosureDataProvider')]
    public function shouldNotClosePhpTag(string $nextSiblingNodeClass): void
    {
        $compiler = $this->createMockCompiler();
        $node = $this->getMockBuilder(ElseIfNode::class)
            ->onlyMethods(['getNextSibling'])
            ->setConstructorArgs(['expression'])
            ->getMock();

        $node->expects(self::once())
            ->method('getNextSibling')
            ->willReturn($this->createMock($nextSiblingNodeClass));

        $node->compile($compiler);

        self::assertStringEndsNotWith('?>', $compiler->getOut());
    }
}
