<?php

namespace App\Controller;

use App\Component\Greeter;
use Sapin\SymfonyBundle\AbstractSapinController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractSapinController
{
    #[Route('/', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderComponent(new Greeter('John'));
    }
}

// Usage without extends:

// #[AsController]
// final class HomePageController
// {
//     use SapinTrait;
//
//     #[Route('/', methods: ['GET'])]
//     public function index(): Response
//     {
//         return $this->renderComponent(new Greeter('John'));
//     }
// }
