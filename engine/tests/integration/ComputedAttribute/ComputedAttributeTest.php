<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ComputedAttribute;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ComputedAttributeTest extends ComponentTestCase
{
    #[Test]
    public function should_render_computed_attribute(): void
    {
        $dom = self::renderComponent(new ComputedAttributeTestComponent());
        self::assertSame(
            'foo',
            trim($dom->body->querySelector('div')->getAttribute('data-test')),
        );
    }
}
