<?php
declare(strict_types=1);

namespace KayStrobach\Migrations\Command;

use KayStrobach\Migrations\Service\DoctrineService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StatusCommand extends \Doctrine\Migrations\Tools\Console\Command\StatusCommand
{
    /** @var string */
    protected static $defaultName = 'migrations:status';

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
        $doctrineService = GeneralUtility::makeInstance(DoctrineService::class);

        $connectionName = $input->getOption('connection') ?? 'Default';
        $this->configuration = $doctrineService->getMigrationConfiguration($connectionName);

        parent::initialize($input, $output);
    }

}
