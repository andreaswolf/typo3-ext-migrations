<?php

namespace KayStrobach\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Implements a doctrine/migration ConfigurationLoader with a TYPO3 specific flavour
 * - Adds
 */
class Typo3ConfigurationLoader implements ConfigurationLoader
{
    public const MIGRATION_TABLE_NAME = 'doctrine_migrationstatus';
    private PackageManager $packageManager;
    private ConnectionPool $connectionPool;

    private LoggerInterface $logger;

    public function __construct(PackageManager $packageManager, ConnectionPool $connectionPool, LogManager $logManager)
    {
        $this->packageManager = $packageManager;
        $this->connectionPool = $connectionPool;
        $this->logger = $logManager->getLogger();
    }

    public function getConfiguration(): Configuration
    {
        $configuration = new Configuration();

        GeneralUtility::mkdir_deep(Environment::getVarPath() . '/migrations');
        $configuration->addMigrationsDirectory(
            'KayStrobach\Migrations\Persistence\Doctrine\Migrations',
            Environment::getVarPath() . '/migrations'
        );

        // Store in our own doctrine_migrationstatus table
        $metadataStorageConfiguration = new TableMetadataStorageConfiguration();
        $metadataStorageConfiguration->setTableName(self::MIGRATION_TABLE_NAME);
        $configuration->setMetadataStorageConfiguration($metadataStorageConfiguration);

        $connection = $this->connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $databasePlatformName = $connection->getDatabasePlatform()->getName();

        foreach ($this->packageManager->getActivePackages() as $package) {
            [$namespace, $path] = $this->getPackageMigrationNamespaceAndDirectory($package);

            if ($namespace === null || $path === null) {
                $this->logger->debug(sprintf('Package %s does not contain any migrations', $package->getPackageKey()));
                continue;
            }

            $plattformPath = $path . ucfirst($databasePlatformName) . '/';

            if (is_dir($plattformPath)) {
                $this->logger->debug(sprintf('Adding migrations for Package %s', $package->getPackageKey()));
                $namespace .= ucfirst($databasePlatformName);
                $configuration->addMigrationsDirectory($namespace, $plattformPath);
            }
        }
        return $configuration;
    }

    /**
     * @return array{0: string|null, 1: string|null} [The namespace, the full package directory]
     */
    private function getPackageMigrationNamespaceAndDirectory(PackageInterface $package): array
    {
        $autoloadComposerDefinition = $package->getValueFromComposerManifest('autoload');

        if (($autoloadComposerDefinition->{'psr-4'} ?? null) instanceof \stdClass) {
            $psr4Namespaces = get_object_vars($autoloadComposerDefinition->{'psr-4'});
            foreach ($psr4Namespaces as $namespace => $dir) {
                if (strpos($namespace, '\\Migrations\\')) {
                    $fullDir = $package->getPackagePath() . $dir . '/';

                    return [$namespace, $fullDir];
                }
            }
        }

        return [null, null];
    }
}
