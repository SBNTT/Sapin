<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\ElseIfNode;
use Sapin\Engine\Ast\Node\Template\ElseNode;
use Sapin\Engine\Ast\Node\Template\IfNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class IfNodeTest extends TestCase
{
    public static function nodesPreventingPhpTagClosureDataProvider(): array
    {
        return [
            [ElseIfNode::class],
            [ElseNode::class]
        ];
    }

    // ---

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new IfNode('expression');

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame(
            '<?php if(expression){?>[children]<?php }?>',
            $compiler->getOut(),
        );
    }

    #[Test, DataProvider('nodesPreventingPhpTagClosureDataProvider')]
    public function shouldNotClosePhpTag(string $nextSiblingNodeClass): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = $this->getMockBuilder(IfNode::class)
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
