<?php

namespace Sapin\Engine\Test\Unit\Ast\Node;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\StyleNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class StyleNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new StyleNode('css');

        $node->compile($compiler);

        self::assertSame('<style>css</style>', $compiler->getOut());
    }
}
