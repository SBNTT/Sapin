<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ComponentCallWithProps;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ComponentCallWithPropsTest extends ComponentTestCase
{
    #[Test]
    public function should_render_sub_component(): void
    {
        $dom = self::renderComponent(new ComponentCallWithPropsTestComponent());

        self::assertSame('Hello, Capybaras 3 times!', trim($dom->body->textContent));
    }
}
