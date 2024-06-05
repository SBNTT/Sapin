<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Compiler;
use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\Ast\Node\Template\ComponentCallNode;
use Sapin\Engine\Ast\Node\Template\SlotContentNode;
use Sapin\Engine\Ast\Node\Template\TemplateElementNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class ComponentCallNodeTest extends TestCase
{
    public static function compilationTestCasesProvider(): array
    {
        return [
            [
                fn (TestCase $context) => ['MyComponent', [], []],
                '<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent());?>',
            ],

            [
                fn (TestCase $context) => ['MyComponent', ['foo' => "'bar'"], []],
                "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(foo:'bar'));?>",
            ],

            [
                fn (TestCase $context) => ['MyComponent', ['foo' => "'bar'", 'buzz' => 'true'], []],
                "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(foo:'bar',buzz:true));?>",
            ],

            [
                fn (TestCase $context) => ['MyComponent', [], [
                    self::createMockTemplateElementNode($context, 'c1'),
                    self::createMockSlotContentNode($context, 'slot1'),
                    self::createMockTemplateElementNode($context, 'c2'),
                ]],
                implode('', [
                    "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(),function(string \$slot,callable \$default){",
                    'switch($slot){',
                    "case'slot1':?>[slot1]<?php break;",
                    "case'children':?>[c1][c2]<?php break;",
                    'default:$default();',
                    '}',
                    '});?>'
                ]),
            ],

            [
                fn (TestCase $context) => ['MyComponent', [], [
                    self::createMockSlotContentNode($context, 'slot1'),
                    self::createMockTemplateElementNode($context, 'c1'),
                    self::createMockSlotContentNode($context, 'slot2'),
                    self::createMockSlotContentNode($context, 'slot3'),
                ]],
                implode('', [
                    "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(),function(string \$slot,callable \$default){",
                    'switch($slot){',
                    "case'slot1':?>[slot1]<?php break;",
                    "case'slot2':?>[slot2]<?php break;",
                    "case'slot3':?>[slot3]<?php break;",
                    "case'children':?>[c1]<?php break;",
                    'default:$default();',
                    '}',
                    '});?>'
                ]),
            ],

            [
                fn (TestCase $context) => ['MyComponent', ['foo' => "'bar'", 'buzz' => 'true'], [
                    self::createMockSlotContentNode($context, 'slot1'),
                    self::createMockSlotContentNode($context, 'slot2'),
                    self::createMockSlotContentNode($context, 'slot3'),
                ]],
                implode('', [
                    "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(foo:'bar',buzz:true),",
                    "function(string \$slot,callable \$default){",
                    'switch($slot){',
                    "case'slot1':?>[slot1]<?php break;",
                    "case'slot2':?>[slot2]<?php break;",
                    "case'slot3':?>[slot3]<?php break;",
                    'default:$default();',
                    '}',
                    '});?>'
                ]),
            ],
        ];
    }

    /**
     * @return MockObject&SlotContentNode
     */
    private static function createMockSlotContentNode(TestCase $context, string $slotName): MockObject
    {
        /**
         * @var MockObject&SlotContentNode $compiler
         * @noinspection PhpUnitInvalidMockingEntityInspection
         */
        $mockNode = $context->getMockBuilder(SlotContentNode::class)
            ->setConstructorArgs([$slotName, $context->createMock(TemplateElementNode::class)])
            ->getMock();

        $mockNode->method('compile')
            ->willReturnCallback(function (Compiler $compiler) use ($slotName) {
                $compiler->write('[' . $slotName . ']');
            });

        return $mockNode;
    }

    /**
     * @return MockObject&TemplateElementNode
     */
    private static function createMockTemplateElementNode(TestCase $context, string $name): MockObject
    {
        $mockNode = $context->createMock(TemplateElementNode::class);

        $mockNode->method('compile')
            ->willReturnCallback(function (Compiler $compiler) use ($name) {
                $compiler->write('[' . $name . ']');
            });

        return $mockNode;
    }

    #[Test, DataProvider('compilationTestCasesProvider')]
    public function shouldCompileCorrectly(callable $nodeParamsBuilder, string $expected): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);

        [$componentFqn, $props, $children] = $nodeParamsBuilder($this);
        $node = new ComponentCallNode($componentFqn, $props);
        $node->addChildren($children);

        $node->compile($compiler);

        self::assertSame($expected, $compiler->getOut());
    }
}
