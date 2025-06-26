<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ChildrenSlot;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ChildrenSlotTest extends ComponentTestCase
{
    #[Test]
    public function should_render_children(): void
    {
        $dom = self::renderComponent(new ChildrenSlotTestComponent());

        self::assertSame('Hello, World!', trim($dom->body->textContent));
    }
}
