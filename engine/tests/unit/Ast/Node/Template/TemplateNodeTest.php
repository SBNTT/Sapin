<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\TemplateNode;
use Sapin\Engine\SapinException;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class TemplateNodeTest extends TestCase
{
    #[Test]
    public function shouldCompileCorrectly(): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new TemplateNode();

        $compiler->expects(self::once())
            ->method('compileNodes')
            ->with($node->getChildren());

        $node->compile($compiler);

        self::assertSame('[children]', $compiler->getOut());
    }

    // ---

    /**
     * @throws \Exception
     */
    #[Test]
    public function shouldCorrectlyAddUse(): void
    {
        $node = new TemplateNode();

        $fqn = 'MyApp\Component\Foo';
        $name = 'Foo';

        self::assertNull($node->getUse($name));
        $node->addUse($name, $fqn);
        self::assertSame($fqn, $node->getUse($name));
    }

    // ---

    /**
     * @throws \Exception
     */
    #[Test]
    public function shouldThrowWhenAddingAnUseWithSameNameTwice(): void
    {
        $node = new TemplateNode();

        $node->addUse('Foo', 'MyApp\Component\Foo');

        $this->expectException(SapinException::class);
        $node->addUse('Foo', 'MyApp\Component\Bar');
    }
}
