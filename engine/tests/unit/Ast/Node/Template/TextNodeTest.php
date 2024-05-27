<?php

namespace Sapin\Engine\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Ast\Node\Template\TextNode;
use Sapin\Engine\Test\Helper\CompilerMockingHelper;

final class TextNodeTest extends TestCase
{
    public static function compilationTestCasesProvider(): array
    {
        return [
            // no transformation expected
            ['', ''],
            ['foo', 'foo'],
            ['foo bar buzz', 'foo bar buzz'],

            // with interpolations
            ['{{ $foo }}', '<?php \\Sapin\\Engine\\Sapin::echo($foo);?>'],
            ['Hello, {{ $this->name }}!', 'Hello, <?php \\Sapin\\Engine\\Sapin::echo($this->name);?>!'],
            ['{{ $key }}:{{ $value }}', '<?php \\Sapin\\Engine\\Sapin::echo($key);?>:<?php \\Sapin\\Engine\\Sapin::echo($value);?>'],
        ];
    }

    #[Test, DataProvider('compilationTestCasesProvider')]
    public function shouldCompileCorrectly(string $value, string $expected): void
    {
        $compiler = CompilerMockingHelper::createMockCompiler($this);
        $node = new TextNode($value);

        $node->compile($compiler);

        self::assertSame($expected, $compiler->getOut());
    }

    // ---

    public static function emptyValuesProvider(): array
    {
        return [['']];
    }

    #[Test, DataProvider('emptyValuesProvider')]
    public function shouldBeConsideredEmpty(string $value): void
    {
        $node = new TextNode($value);
        $this->assertTrue($node->isEmpty());
    }

    // ---

    public static function nonEmptyValuesProvider(): array
    {
        return [[' '], ['foo'], ['foo bar'], ['foo-{{ bar }}']];
    }

    #[Test, DataProvider('nonEmptyValuesProvider')]
    public function shouldNotBeConsideredEmpty(string $value): void
    {
        $node = new TextNode($value);
        $this->assertFalse($node->isEmpty());
    }
}
