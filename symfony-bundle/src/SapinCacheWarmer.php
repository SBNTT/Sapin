<?php

namespace Sapin\SymfonyBundle;

use Sapin\Engine\Sapin;
use Sapin\Engine\SapinException;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class SapinCacheWarmer implements CacheWarmerInterface
{
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @throws SapinException
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        Sapin::warmUpCache();

        return [];
    }
}
