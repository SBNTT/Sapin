<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ForLoop;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ForLoopTest extends ComponentTestCase
{
    #[Test]
    public function should_render_list_of_int(): void
    {
        $times = 10;
        $dom = self::renderComponent(new ForLoopTestComponent($times));

        $ul = $dom->body->querySelector('ul');
        self::assertNotNull($ul);

        self::assertSame($times, $ul->childElementCount);

        for ($i = 0; $i < $times; ++$i) {
            self::assertSame((string) $i, trim($ul->childNodes->item($i)->textContent));
        }
    }
}
