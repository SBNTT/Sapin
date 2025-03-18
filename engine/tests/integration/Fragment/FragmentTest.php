<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\Fragment;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class FragmentTest extends ComponentTestCase
{
    #[Test]
    public function should_not_render_fragment_tag(): void
    {
        $dom = self::renderComponent(new FragmentTestComponent());

        self::assertSame(3, $dom->body->childElementCount);
        self::assertSame('h1', strtolower($dom->body->childNodes->item(0)->nodeName));
        self::assertSame('h2', strtolower($dom->body->childNodes->item(1)->nodeName));
        self::assertSame('p', strtolower($dom->body->childNodes->item(2)->nodeName));
    }
}
