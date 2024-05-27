<?php

namespace Sapin\SymfonyBundle;

use Sapin\Engine\Sapin;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SapinBundle extends AbstractBundle
{
    public function boot(): void
    {
        Sapin::configure(
            cacheDirectory: $this->container->getParameter('kernel.cache_dir') . '/components',
            disableIncrementalCompilation: $this->container->getParameter('kernel.environment') === 'dev',
        );
    }
}
