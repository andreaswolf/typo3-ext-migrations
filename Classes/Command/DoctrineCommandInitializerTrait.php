<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use AndreasWolf\Migrations\Service\DoctrineService;
use Doctrine\Migrations\Configuration\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait DoctrineCommandInitializerTrait
{
    private DoctrineService $doctrineService;

    public function __construct(DoctrineService $doctrineService)
    {
        $this->doctrineService = $doctrineService;
        parent::__construct();
    }

    // use this for additional configurations in your command
    protected function additionalConfiguration(): void
    {

    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'connection',
            null,
            InputOption::VALUE_OPTIONAL,
            'The DB connection to use.',
            'Default'
        );

        $this->addOption(
            'packageKey',
            null,
            InputOption::VALUE_OPTIONAL,
            'The specific package to scope to.',
        );

        $this->additionalConfiguration();
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($input->hasOption('dry-run')) {
            $dryRun = (bool) $input->getOption('dry-run');
            $this->doctrineService->setDryRun($dryRun);
        }

        $connectionName = $input->getOption('connection') ?? 'Default';
        $packageKey = $input->getOption('packageKey') ?: null;

        if ($packageKey === null && $this->isPackageKeyRequired()) {
            throw new \Exception('Package-Key is required for this command');
        }

        $this->configuration = $this->doctrineService->getMigrationConfiguration($connectionName, $packageKey);

        parent::initialize($input, $output);
    }

    protected function isPackageKeyRequired(): bool
    {
        return false;
    }
}
