<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\StatusCommand as DoctrineStatusCommand;
use KayStrobach\Migrations\Service\DoctrineService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StatusCommand extends DoctrineStatusCommand
{
    private DoctrineService $doctrineService;

    public function __construct(DoctrineService $doctrineService = null)
    {
        $this->doctrineService = $doctrineService ?? GeneralUtility::makeInstance(DoctrineService::class);
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'connection',
            null,
            InputOption::VALUE_OPTIONAL,
            'The DB connection to use.'
        );
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $connectionName = $input->getOption('connection') ?? 'Default';
        $this->configuration = $this->doctrineService->getMigrationConfiguration($connectionName);

        parent::initialize($input, $output);
    }
}
