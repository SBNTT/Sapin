<?php

namespace Sapin\SymfonyBundle;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractSapinController extends AbstractController
{
    use SapinTrait;
}
