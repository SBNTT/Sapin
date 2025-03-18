<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ForEachLoop;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;
use function count;

/**
 * @internal
 * @small
 */
final class ForEachLoopTest extends ComponentTestCase
{
    #[Test]
    public function should_render_list_of_fruits(): void
    {
        $fruits = ['apple', 'orange', 'banana'];
        $dom = self::renderComponent(new ForEachLoopTestComponentA($fruits));

        $ul = $dom->body->querySelector('ul');
        self::assertNotNull($ul);

        self::assertSame(count($fruits), $ul->childElementCount);

        foreach ($fruits as $index => $fruit) {
            self::assertSame($fruit, trim($ul->childNodes->item($index)->textContent));
        }
    }

    #[Test]
    public function should_render_filtered_list_of_numbers(): void
    {
        $numbers = [0, 1, 2, 3, 4, 5, 6];
        $evenNumbers = [0, 2, 4, 6];

        $dom = self::renderComponent(new ForEachLoopTestComponentB($numbers));

        $ul = $dom->body->querySelector('ul');
        self::assertNotNull($ul);

        self::assertSame(count($evenNumbers), $ul->childElementCount);

        foreach ($evenNumbers as $index => $number) {
            self::assertSame((string) $number, trim($ul->childNodes->item($index)->textContent));
        }
    }
}
