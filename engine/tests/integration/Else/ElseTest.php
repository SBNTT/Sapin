<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\Else;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ElseTest extends ComponentTestCase
{
    #[Test]
    public function should_render_else_branch(): void
    {
        $dom = self::renderComponent(new ElseTestComponent(10, 9));
        self::assertSame('a is not the same as b!', trim($dom->body->textContent));
    }

    #[Test]
    public function should_not_render_else_branch(): void
    {
        $dom = self::renderComponent(new ElseTestComponent(10, 10));
        self::assertNotSame('a is not the same as b!', trim($dom->body->textContent));
    }
}
