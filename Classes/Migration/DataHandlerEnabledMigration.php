<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use KayStrobach\Migrations\Service\DoctrineMigrationCoordinator;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DataHandlerEnabledMigration extends AbstractMigration
{
    /** @var BackendUserAuthentication */
    protected $backendUser;

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
     * @return BackendUserAuthentication
     */
    protected function createMockCliUser(): BackendUserAuthentication
    {
        $backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $backendUser->uc = [];
        $backendUser->user = ['uid' => 999999, 'username' => '_cli_migration', 'admin' => 1];
        $backendUser->workspace = 0;
        return $backendUser;
    }

    /**
     * Creates a DataHandler instance with the given data and command maps.
     *
     * @return DataHandler
     */
    private function createDataHandlerInstance(array $dataMap = [], array $commandMap = []): DataHandler
    {
        $backendUser = $this->createMockCliUser();
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

        $dataHandler->start($dataMap, $commandMap, $backendUser);

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
