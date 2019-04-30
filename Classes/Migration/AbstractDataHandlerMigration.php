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

/**
 * Base class for migrations using the TYPO3 DataHandler
 */
abstract class AbstractDataHandlerMigration extends AbstractMigration
{
    /**
     * Static data map for the DataHandler
     *
     * @var array
     */
    protected $dataMap = [];

    /**
     * Static command map for the DataHandler
     *
     * @var array
     */
    protected $commandMap = [];
    /**
     * Run static data/command map
     *
     * This method should be overwritten if more complex logic is required
     *
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $dataHandler = $this->getDataHandler($this->dataMap, $this->commandMap);
        $dataHandler->process_datamap();
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $reflectionClass = new \ReflectionClass($this);
        $version = str_replace('Version', '', $reflectionClass->getShortName());

        $this->getMigrationCoordinator()->setCurrentVersion($version);
    }


    public function postUp(Schema $schema): void
    {
        $this->getMigrationCoordinator()->resetCurrentVersion();

        parent::postUp($schema);
    }

    /**
     * Creates a DataHandler instance with the given data and command maps.
     *
     * @return DataHandler
     */
    protected function getDataHandler(array $dataMap = [], array $commandMap = []): DataHandler
    {
        $doctrineService = GeneralUtility::makeInstance(DoctrineService::class);

        if ($doctrineService->isDryRun()) {
            $dataHandlerClass = DryRunDataHandler::class;
        } else {
            $dataHandlerClass = DataHandler::class;
        }

        $dataHandler = GeneralUtility::makeInstance($dataHandlerClass);
        $dataHandler->start($dataMap, $commandMap);

        return $dataHandler;
    }

    /**
     * @return DoctrineMigrationCoordinator
     */
    private function getMigrationCoordinator()
    {
        return GeneralUtility::makeInstance(DoctrineMigrationCoordinator::class);
    }
}
