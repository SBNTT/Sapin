<?php

namespace Sapin\Engine\Test\Unit\Ast\Node;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\ScopedStyleNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class ScopedStyleNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new ScopedStyleNode('css', 'scopeId');

        $node->compile($compiler);

        self::assertSame(
            '<style>@scope ([data-scope="scopeId"]) to ([data-scope]){css}</style>',
            $compiler->getOut(),
        );
    }
}
