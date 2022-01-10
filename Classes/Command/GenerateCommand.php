<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\GenerateCommand as DoctrineGenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateCommand extends DoctrineGenerateCommand
{
    use DoctrineCommandInitializerTrait;

    protected function isPackageKeyRequired(): bool
    {
        return true;
    }

    protected function additionalConfiguration(): void
    {
        $this->addOption(
            'dataHandlerMigration',
            't',
            InputOption::VALUE_NONE,
            'Generate a TYPO3 Migration with DataHandler instead of a normal SQL Migration'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if ($input->getOption('dataHandlerMigration')) {
            $this->configuration->setCustomTemplate(__DIR__ . '/../../Resources/Private/Template/datahandlerMigration.php.tpl');
        }
        return parent::execute($input, $output);
    }
}
