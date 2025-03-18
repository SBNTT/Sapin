<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\Slot;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertSame;

/**
 * @internal
 * @small
 */
final class SlotTest extends ComponentTestCase
{
    #[Test]
    public function should_render_provided_slots_content(): void
    {
        $dom = self::renderComponent(new SlotTestComponentA());

        assertSame(
            'My card title',
            trim($dom->body->querySelector('.card-title')?->textContent),
        );

        assertSame(
            'My card content',
            trim($dom->body->querySelector('.card-content')?->textContent),
        );

        assertSame(
            'My card footer',
            trim($dom->body->querySelector('.card-footer')?->textContent),
        );
    }

    #[Test]
    public function should_render_default_slots_content(): void
    {
        $dom = self::renderComponent(new SlotTestComponentB());

        assertSame(
            'Default title',
            trim($dom->body->querySelector('.card-title')?->textContent),
        );

        assertEmpty(trim($dom->body->querySelector('.card-content')?->textContent));

        assertSame(
            'Default footer',
            trim($dom->body->querySelector('.card-footer')?->textContent),
        );
    }
}
