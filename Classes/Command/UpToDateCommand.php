<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand as DoctrineUpToDateCommand;

final class UpToDateCommand extends DoctrineUpToDateCommand
{
    use DoctrineCommandInitializerTrait;
}
