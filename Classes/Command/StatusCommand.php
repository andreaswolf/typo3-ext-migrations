<?php
declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\StatusCommand as DoctrineStatusCommand;

final class StatusCommand extends DoctrineStatusCommand
{
    use DoctrineCommandInitializerTrait;
}
