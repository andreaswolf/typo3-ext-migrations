<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Tester;

use KayStrobach\Migrations\Command\MigrateCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Helper class to run the doctrine/migrations CLI commands within functional tests.
 */
final class DoctrineCommandRunner
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
