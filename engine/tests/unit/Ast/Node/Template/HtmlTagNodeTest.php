<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Node\Template\HtmlTagAttributeNode;
use Sapin\Ast\Node\Template\HtmlTagNode;
use Sapin\Ast\Node\Template\TextNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class HtmlTagNodeTest extends TestCase
{
    use CompilerMockingHelper;

    public static function compilationTestCasesProvider(): array
    {
        return [
            [
                fn (TestCase $context) => ['tagName', []],
                '<tagname>[children]</tagname>',
            ],
            [
                fn (TestCase $context) => ['tagName', [
                    $context->createMock(HtmlTagAttributeNode::class)
                ]],
                '<tagname [child]>[children]</tagname>',
            ],
            [
                fn (TestCase $context) => ['tagName', [
                    $context->createMock(HtmlTagAttributeNode::class),
                    $context->createMock(HtmlTagAttributeNode::class),
                ]],
                '<tagname [child] [child]>[children]</tagname>',
            ],
        ];
    }

    #[Test, DataProvider('compilationTestCasesProvider')]
    public function shouldCompileCorrectly(callable $nodeParamsBuilder, $expected): void
    {
        $compiler = $this->createMockCompiler();

        [$name, $attributes] = $nodeParamsBuilder($this);
        $node = new HtmlTagNode($name, $attributes);

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $compileNodeMatcher = self::exactly(count($attributes));
        $compiler->expects($compileNodeMatcher)
            ->method('compileNode')
            ->with(self::callback(function($param) use ($attributes, $compileNodeMatcher) {
                self::assertSame($param, $attributes[$compileNodeMatcher->numberOfInvocations() - 1]);
                return true;
            }));

        $node->compile($compiler);

        self::assertSame($expected, $compiler->getOut());
    }
}
