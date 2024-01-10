<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Tester;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Helper class to run the doctrine/migrations CLI commands within functional tests.
 */
final class DoctrineCommandRunner
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function executeMigrateCommand(): void
    {
        $command = $this->container->get(MigrateCommand::class);
        $command->setHelperSet(new HelperSet([
            'question' => new QuestionHelper(),
        ]));
        $commandTester = new CommandTester($command);

        $commandTester->execute(['--no-interaction']);

        $commandTester->assertCommandIsSuccessful();
    }
}
