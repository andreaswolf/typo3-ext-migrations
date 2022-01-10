<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\RollupCommand as DoctrineRollupCommand;

class RollupCommand extends DoctrineRollupCommand
{
    use DoctrineCommandInitializerTrait;
}
