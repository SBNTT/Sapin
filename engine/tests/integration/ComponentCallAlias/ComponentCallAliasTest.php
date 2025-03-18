<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Integration\ComponentCallAlias;

use PHPUnit\Framework\Attributes\Test;
use Sapin\Engine\Test\Helper\ComponentTestCase;

/**
 * @internal
 * @small
 */
final class ComponentCallAliasTest extends ComponentTestCase
{
    #[Test]
    public function should_render_sub_component(): void
    {
        $dom = self::renderComponent(new ComponentCallAliasTestComponent());

        self::assertSame('Hello, World!', trim($dom->body->textContent));
    }
}
