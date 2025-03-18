<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\Interpolation;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class InterpolationTest extends ComponentTestCase
{
    #[Test]
    public function should_render_variable(): void
    {
        $dom = self::renderComponent(new InterpolationTestComponent(name: 'Capybaras'));

        self::assertSame(
            'Hello, Capybaras!',
            trim($dom->body->textContent),
        );

        self::assertSame(
            'Hello, Capybaras!',
            $dom->body->querySelector('span')->getAttribute('data-content'),
        );
    }
}
