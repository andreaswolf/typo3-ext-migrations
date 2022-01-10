<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\DiffCommand as DoctrineDiffCommand;

final class DiffCommand extends DoctrineDiffCommand
{
    use DoctrineCommandInitializerTrait;
}
