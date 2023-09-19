<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use KayStrobach\Migrations\DataHandling\DryRunDataHandler;
use KayStrobach\Migrations\Service\DoctrineMigrationCoordinator;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
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
     * @var array<mixed>
     */
    protected array $dataMap = [];

    /**
     * Static command map for the DataHandler
     *
     * @var array<mixed>
     */
    protected array $commandMap = [];

    private DoctrineMigrationCoordinator $doctrineMigrationCoordinator;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);

        $this->doctrineMigrationCoordinator = new DoctrineMigrationCoordinator();
        $this->doctrineMigrationCoordinator->setCurrentVersion(static::class);
    }

    /**
     * Run static data/command map
     *
     * This method should be overwritten if more complex logic is required
     */
    public function up(Schema $schema): void
    {
        $dataHandler = $this->getDataHandler($this->dataMap, $this->commandMap);

        $dataHandler->process_datamap();
        $dataHandler->process_cmdmap();

        if (count($dataHandler->errorLog) > 0) {
            foreach ($dataHandler->errorLog as $error) {
                $this->write($error);
            }

            throw new \RuntimeException('DataHandler execution failed, see errors above', 1556788267);
        }
    }

    public function down(Schema $schema): void
    {
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $reflectionClass = new \ReflectionClass($this);
        $version = str_replace('Version', '', $reflectionClass->getShortName());

        $this->getMigrationCoordinator()->setCurrentVersion($version);

        Bootstrap::initializeBackendAuthentication(true);
    }

    public function postUp(Schema $schema): void
    {
        $this->getMigrationCoordinator()->resetCurrentVersion();

        parent::postUp($schema);
    }

    public function preDown(Schema $schema): void
    {
        Bootstrap::initializeBackendAuthentication(true);
    }

    /**
     * Creates a DataHandler instance with the given data and command maps.
     *
     * @param array<mixed> $dataMap
     * @param array<mixed> $commandMap
     */
    protected function getDataHandler(array $dataMap = [], array $commandMap = []): DataHandler
    {
        // @todo Find a way to pass the information about --dry-run from the Command to here:
        #$dataHandlerClass = $isDryRun ? DryRunDataHandler::class : DataHandler::class;
        $dataHandlerClass = DataHandler::class;

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance($dataHandlerClass);
        $dataHandler->start($dataMap, $commandMap);

        return $dataHandler;
    }

    private function getMigrationCoordinator(): DoctrineMigrationCoordinator
    {
        return GeneralUtility::makeInstance(DoctrineMigrationCoordinator::class);
    }
}
