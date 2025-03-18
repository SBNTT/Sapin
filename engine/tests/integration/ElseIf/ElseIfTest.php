<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ElseIf;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ElseIfTest extends ComponentTestCase
{
    #[Test]
    public function should_render_else_if_branch(): void
    {
        $dom = self::renderComponent(new ElseIfTestComponent(9, 10));
        self::assertSame('a is smaller than b!', trim($dom->body->textContent));
    }

    #[Test]
    public function should_not_render_else_if_branch(): void
    {
        $dom = self::renderComponent(new ElseIfTestComponent(10, 9));
        self::assertNotSame('a is smaller than b!', trim($dom->body->textContent));
    }
}
