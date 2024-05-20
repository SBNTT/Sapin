<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\ElseNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class ElseNodeTest extends TestCase
{
    use CompilerMockingHelper;

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = $this->createMockCompiler();
        $node = new ElseNode();

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame(
            'else{?>[children]<?php }?>',
            $compiler->getOut(),
        );
    }
}
