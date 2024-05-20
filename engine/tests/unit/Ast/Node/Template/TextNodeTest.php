<?php

namespace Sapin\Test\Unit\Ast\Node\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sapin\Ast\Compiler;
use Sapin\Ast\Node\Template\TextNode;
use Sapin\Test\Helper\CompilerMockingHelper;

final class TextNodeTest extends TestCase
{
    use CompilerMockingHelper;

    public static function compilationTestCasesProvider(): array
    {
        return [
            // no transformation expected
            ['', ''],
            ['foo', 'foo'],
            ['foo bar buzz', 'foo bar buzz'],

            // with interpolations
            ['{{ $foo }}', '<?php \\Sapin\\Sapin::echo($foo);?>'],
            ['Hello, {{ $this->name }}!', 'Hello, <?php \\Sapin\\Sapin::echo($this->name);?>!'],
            ['{{ $key }}:{{ $value }}', '<?php \\Sapin\\Sapin::echo($key);?>:<?php \\Sapin\\Sapin::echo($value);?>'],
        ];
    }

    #[Test, DataProvider('compilationTestCasesProvider')]
    public function shouldCompileCorrectly(string $value, string $expected): void
    {
        $compiler = $this->createMockCompiler();
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
