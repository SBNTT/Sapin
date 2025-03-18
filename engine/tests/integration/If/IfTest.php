<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\If;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class IfTest extends ComponentTestCase
{
    #[Test]
    public function should_render_something(): void
    {
        $dom = self::renderComponent(new IfTestComponent(10, 9));

        self::assertNotEmpty(trim($dom->body->textContent));
    }

    #[Test]
    public function should_render_nothing(): void
    {
        $dom = self::renderComponent(new IfTestComponent(9, 10));

        self::assertEmpty(trim($dom->body->textContent));
    }
}
