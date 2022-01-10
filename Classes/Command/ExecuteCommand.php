<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand as DoctrineExecuteCommand;

final class ExecuteCommand extends DoctrineExecuteCommand
{
    use DoctrineCommandInitializerTrait;
}
