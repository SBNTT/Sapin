<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\RawText;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class RawTextTest extends ComponentTestCase
{
    #[Test]
    public function should_render_hello_world(): void
    {
        $dom = self::renderComponent(new RawTextTestComponent());

        self::assertSame('Hello, World!', trim($dom->body->textContent));
    }
}
