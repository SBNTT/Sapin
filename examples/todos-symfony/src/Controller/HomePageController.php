<?php

namespace App\Controller;

use App\Component\Greeter;
use Sapin\Engine\Sapin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    #[Route('/', methods: ['GET'])]
    public function index(): Response
    {
        Sapin::configure(
            cacheDirectory: $this->kernel->getCacheDir().'/components',
        );

        return new Response(Sapin::renderToString(new Greeter('John')));
    }
}
