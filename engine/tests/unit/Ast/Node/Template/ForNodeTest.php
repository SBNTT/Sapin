<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\ForNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class ForNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new ForNode('expression');

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame(
            '<?php for(expression){?>[children]<?php }?>',
            $compiler->getOut(),
        );
    }
}
