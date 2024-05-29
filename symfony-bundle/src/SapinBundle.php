<?php

namespace Sapin\SymfonyBundle;

use Sapin\Engine\Sapin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SapinBundle extends AbstractBundle
{
    public function boot(): void
    {
        Sapin::configure(
            cacheDirectory: $this->container->getParameter('kernel.cache_dir') . '/sapin',
            disableIncrementalCompilation: $this->container->getParameter('kernel.environment') === 'dev',
        );
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->services()
            ->set('sapin.cache_warmer', SapinCacheWarmer::class)
                ->tag('kernel.cache_warmer');
    }
}
