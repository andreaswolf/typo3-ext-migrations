<?php

return [
    'migrations:diff' => [
        'class' => \KayStrobach\Migrations\Command\DiffCommand::class,
        'schedulable' => false,
    ],
    'migrations:execute' => [
        'class' => \KayStrobach\Migrations\Command\ExecuteCommand::class,
        'schedulable' => false,
    ],
    'migrations:migrate' => [
        'class' => \KayStrobach\Migrations\Command\MigrateCommand::class,
        'schedulable' => false,
    ],
    'migrations:rollup' => [
        'class' => \KayStrobach\Migrations\Command\RollupCommand::class,
        'schedulable' => false,
    ],
    'migrations:uptodate' => [
        'class' => \KayStrobach\Migrations\Command\UpToDateCommand::class,
        'schedulable' => false,
    ],
    'migrations:status' => [
        'class' => \KayStrobach\Migrations\Command\StatusCommand::class,
        'schedulable' => false,
    ],
    'migrations:version' => [
        'class' => \KayStrobach\Migrations\Command\VersionCommand::class,
        'schedulable' => false,
    ],
];
