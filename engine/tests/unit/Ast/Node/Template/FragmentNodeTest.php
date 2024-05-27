<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\FragmentNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class FragmentNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new FragmentNode();

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame('[children]', $compiler->getOut());
    }
}
