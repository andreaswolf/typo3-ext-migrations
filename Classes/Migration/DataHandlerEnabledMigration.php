<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use KayStrobach\Migrations\DataHandling\DryRunDataHandler;
use KayStrobach\Migrations\Service\DoctrineMigrationCoordinator;
use KayStrobach\Migrations\Service\DoctrineService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DataHandlerEnabledMigration extends AbstractMigration
{
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $this->setCurrentMigrationVersion();
    }

    public function up(Schema $schema): void
    {
        $dataMap = [];

        $dataHandler = $this->createDataHandlerInstance($dataMap, []);
        $dataHandler->process_datamap();
    }

    public function postUp(Schema $schema): void
    {
        $this->getMigrationCoordinator()->resetCurrentVersion();

        parent::postUp($schema);
    }

    /**
     * @return DoctrineMigrationCoordinator
     */
    private function getMigrationCoordinator()
    {
        return GeneralUtility::makeInstance(DoctrineMigrationCoordinator::class);
    }

    /**
     * Creates a DataHandler instance with the given data and command maps.
     *
     * @return DataHandler
     */
    private function createDataHandlerInstance(array $dataMap = [], array $commandMap = []): DataHandler
    {
        $doctrineService = GeneralUtility::makeInstance(DoctrineService::class);

        if ($doctrineService->isDryRun()) {
            $dataHandler = GeneralUtility::makeInstance(DryRunDataHandler::class);
        } else {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        }

        $dataHandler->start($dataMap, $commandMap);

        return $dataHandler;
    }

    private function setCurrentMigrationVersion(): void
    {
        $migrationCoordinator = $this->getMigrationCoordinator();
        $reflect = new \ReflectionClass($this);
        $version = str_replace('Version', '', $reflect->getShortName());
        $migrationCoordinator->setCurrentVersion($version);
    }
}
