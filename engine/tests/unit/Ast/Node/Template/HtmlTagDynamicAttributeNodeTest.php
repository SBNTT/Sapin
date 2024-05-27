<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\HtmlTagDynamicAttributeNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class HtmlTagDynamicAttributeNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new HtmlTagDynamicAttributeNode('name', 'expression');

        $node->compile($compiler);

        self::assertSame(
            'name="<?php \\Sapin\\Engine\\Sapin::echo(expression);?>"',
            $compiler->getOut(),
        );
    }
}
