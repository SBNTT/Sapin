<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Sapin\Engine\Sapin;

Sapin::configure(
    cacheDirectory: __DIR__ . '/../var/cache/components',
);

Sapin::warmUpCache();
