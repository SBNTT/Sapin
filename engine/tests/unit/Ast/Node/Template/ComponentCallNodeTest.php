<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Compiler;
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
                    $context->createMock(TemplateElementNode::class),
                    self::createMockSlotContentNode($context, 'slot1'),
                    $context->createMock(TemplateElementNode::class),
                ]],
                implode('', [
                    "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(),function(string \$slot,callable \$default){",
                    "if(\$slot==='slot1'){?>[slot1]<?php }",
                    "else{\$default();}",
                    "});?>",
                ]),
            ],

            [
                fn (TestCase $context) => ['MyComponent', [], [
                    self::createMockSlotContentNode($context, 'slot1'),
                    $context->createMock(TemplateElementNode::class),
                    self::createMockSlotContentNode($context, 'slot2'),
                    self::createMockSlotContentNode($context, 'slot3'),
                ]],
                implode('', [
                    "<?php \\Sapin\\Engine\\Sapin::render(new \\MyComponent(),function(string \$slot,callable \$default){",
                    "if(\$slot==='slot1'){?>[slot1]<?php }",
                    "else if(\$slot==='slot2'){?>[slot2]<?php }",
                    "else if(\$slot==='slot3'){?>[slot3]<?php }",
                    "else{\$default();}",
                    "});?>",
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
                    "if(\$slot==='slot1'){?>[slot1]<?php }",
                    "else if(\$slot==='slot2'){?>[slot2]<?php }",
                    "else if(\$slot==='slot3'){?>[slot3]<?php }",
                    "else{\$default();}",
                    "});?>",
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
