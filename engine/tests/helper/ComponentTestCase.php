<?php

declare(strict_types=1);

namespace Sapin\Engine\Test\Helper;

use Dom\HTMLDocument;
use PHPUnit\Framework\TestCase;
use Sapin\Engine\Component;
use Sapin\Engine\ComponentLoaderInterface;
use Sapin\Engine\Sapin;
use const LIBXML_NOERROR;

abstract class ComponentTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Sapin::configure('.phpunit.cache/sapin');
    }

    protected static function renderComponent(Component|ComponentLoaderInterface $component): HTMLDocument
    {
        $html = Sapin::renderToString($component);

        return HTMLDocument::createFromString($html, LIBXML_NOERROR);
    }
}
