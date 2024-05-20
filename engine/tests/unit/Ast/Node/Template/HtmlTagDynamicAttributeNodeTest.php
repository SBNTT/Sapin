<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\HtmlTagDynamicAttributeNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class HtmlTagDynamicAttributeNodeTest extends TestCase
{
    use CompilerMockingHelper;

    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = $this->createMockCompiler();
        $node = new HtmlTagDynamicAttributeNode('name', 'expression');

        $node->compile($compiler);

        self::assertSame(
            'name="<?php \Sapin\Sapin::echo(expression);?>"',
            $compiler->getOut(),
        );
    }
}
