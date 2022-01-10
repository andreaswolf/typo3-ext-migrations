<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;

class MigrateCommand extends DoctrineMigrateCommand
{
    use DoctrineCommandInitializerTrait;
}
