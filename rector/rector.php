<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../*.php',
        __DIR__ . '/../Classes/',
        __DIR__ . '/../Resources/',
        __DIR__ . '/../Tests/',
    ])
    ->withSkip([
        __DIR__,
        __DIR__ . '/../vendor/',
        __DIR__ . '/../vendor/',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php80: true)
    ->withTypeCoverageLevel(0);
