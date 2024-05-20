<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\FragmentNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class FragmentNodeTest extends TestCase
{
    use CompilerMockingHelper;

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = $this->createMockCompiler();
        $node = new FragmentNode();

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame('[children]', $compiler->getOut());
    }
}
