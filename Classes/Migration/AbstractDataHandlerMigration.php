<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Version\Version;
use KayStrobach\Migrations\DataHandling\DryRunDataHandler;
use KayStrobach\Migrations\Service\DoctrineMigrationCoordinator;
use KayStrobach\Migrations\Service\DoctrineService;
use Psr\Log\LoggerAwareInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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

    private DoctrineService $doctrineService;


    public function __construct(Version $version)
    {
        parent::__construct($version);

        $this->doctrineMigrationCoordinator = new DoctrineMigrationCoordinator();
        $this->doctrineMigrationCoordinator->setCurrentVersion(static::class);

        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < 100000) {
            $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
            $packageManager = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService);
        } else {
            $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        }

        // todo: refactor this with proper dependency injection if possible
        /** @var DoctrineService $doctrineService */
        $doctrineService = GeneralUtility::makeInstance(
            DoctrineService::class,
            $packageManager,
            GeneralUtility::makeInstance(ConnectionPool::class),
            GeneralUtility::makeInstance(LogManager::class),
        );
        $this->doctrineService = $doctrineService;
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

    public function preDown(Schema $schema) : void
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
        $dataHandlerClass = $this->doctrineService->isDryRun() ? DryRunDataHandler::class : DataHandler::class;

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
