<?php
declare(strict_types=1);

namespace KayStrobach\Migrations\Command;

use KayStrobach\Migrations\Service\DoctrineService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExecuteCommand extends \Doctrine\Migrations\Tools\Console\Command\ExecuteCommand
{
    /** @var string */
    protected static $defaultName = 'migrations:execute';

    private DoctrineService $doctrineService;

    public function __construct(DoctrineService $doctrineService)
    {
        $this->doctrineService = $doctrineService;
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
        $dryRun         = (bool) $input->getOption('dry-run');
        $this->doctrineService->setDryRun($dryRun);

        $connectionName = $input->getOption('connection') ?? 'Default';
        $this->configuration = $this->doctrineService->getMigrationConfiguration($connectionName);

        parent::initialize($input, $output);
    }

}
