<?php

declare(strict_types = 1);

namespace AndreasWolf\Migrations\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use AndreasWolf\Migrations\DataHandling\DryRunDataHandler;
use AndreasWolf\Migrations\Service\DoctrineMigrationCoordinator;
use AndreasWolf\Migrations\Service\DoctrineService;
use Doctrine\Migrations\Version\Version;
use Psr\Log\LoggerAwareInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base class for migrations using the TYPO3 DataHandler
 */
abstract class AbstractDataHandlerMigration extends AbstractMigration implements LoggerAwareInterface
{
    protected DoctrineMigrationCoordinator $doctrineMigrationCoordinator;
    protected DoctrineService $doctrineService;

    /**
     * Static data map for the DataHandler
     * @phpstan-var array<mixed> $dataMap
     */
    protected array $dataMap = [];

    /**
     * Static command map for the DataHandler
     * @phpstan-var array<mixed>
     */
    protected array $commandMap = [];

    public function __construct(Version $version)
    {
        parent::__construct($version);
        $this->doctrineMigrationCoordinator = new DoctrineMigrationCoordinator();
        $this->doctrineMigrationCoordinator->setCurrentVersion(static::class);
        // todo: refactor this with proper dependency injection if possible
        /** @var DoctrineService $doctrineService */
        $doctrineService = GeneralUtility::makeInstance(
            DoctrineService::class,
            GeneralUtility::makeInstance(PackageManager::class),
            GeneralUtility::makeInstance(ConnectionPool::class),
            GeneralUtility::makeInstance(LogManager::class),
        );
        $this->doctrineService = $doctrineService;
    }

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
        $dataHandler->process_cmdmap();

        if (count($dataHandler->errorLog) > 0) {
            foreach ($dataHandler->errorLog as $error) {
                $this->write($error);
            }

            throw new \RuntimeException('DataHandler execution failed, see errors above', 1556788267);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    public function preUp(Schema $schema): void
    {
        Bootstrap::initializeBackendAuthentication();
    }

    public function preDown(Schema $schema) : void
    {
        Bootstrap::initializeBackendAuthentication();
    }

    /**
     * Creates a DataHandler instance with the given data and command maps.
     *
     * @param array<mixed> $dataMap
     * @param array<mixed> $commandMap
     * @return DataHandler
     */
    protected function getDataHandler(array $dataMap = [], array $commandMap = []): DataHandler
    {
        $dataHandlerClass = $this->doctrineService->isDryRun() ? DryRunDataHandler::class : DataHandler::class;

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance($dataHandlerClass);
        $dataHandler->start($dataMap, $commandMap);

        return $dataHandler;
    }
}
