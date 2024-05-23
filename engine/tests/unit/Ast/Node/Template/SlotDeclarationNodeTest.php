<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Compiler;
use Sapin\Ast\Node\Template\SlotDeclarationNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class SlotDeclarationNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new SlotDeclarationNode('slotName');

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        $expected = implode('', [
            '<?php $defaultSlotRenderer = function(){?>[children]<?php };',
            '$slotRenderer === null ? $defaultSlotRenderer() : $slotRenderer(\'slotName\', $defaultSlotRenderer);?>'
        ]);
        self::assertSame($expected, $compiler->getOut());
    }
}
