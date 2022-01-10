<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\VersionCommand as DoctrineVersionCommand;

final class VersionCommand extends DoctrineVersionCommand
{
    use DoctrineCommandInitializerTrait;
}
