<?php

namespace Sapin\SymfonyBundle;

use Sapin\Engine\Sapin;
use Sapin\Engine\SapinException;
use Symfony\Component\HttpFoundation\Response;

trait SapinTrait
{
    /**
     * @throws SapinException
     */
    public function renderComponent(object $component, ?Response $response = null): Response
    {
        $response ??= new Response();
        $response->setContent(Sapin::renderToString($component));

        return $response;
    }
}
