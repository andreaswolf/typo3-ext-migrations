<?php

return [
    'doctrine_diff' => [
        'class' => \KayStrobach\Migrations\Command\DiffCommand::class,
        'schedulable' => false,
    ],
    'doctrine_execute' => [
        'class' => \KayStrobach\Migrations\Command\ExecuteCommand::class,
        'schedulable' => false,
    ],
    'doctrine_migrate' => [
        'class' => \KayStrobach\Migrations\Command\MigrateCommand::class,
        'schedulable' => false,
    ],
    'doctrine_rollup' => [
        'class' => \KayStrobach\Migrations\Command\RollupCommand::class,
        'schedulable' => false,
    ],
    'doctrine_uptodate' => [
        'class' => \KayStrobach\Migrations\Command\UpToDateCommand::class,
        'schedulable' => false,
    ],
    'doctrine_status' => [
        'class' => \KayStrobach\Migrations\Command\StatusCommand::class,
        'schedulable' => false,
    ],
    'doctrine_version' => [
        'class' => \KayStrobach\Migrations\Command\VersionCommand::class,
        'schedulable' => false,
    ],
];
