<?php

namespace KayStrobach\Migrations\UpgradeWizard;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates the version number in the `doctrine_migrationstatus` table
 *
 * Transforms version number (i.e. '20230912174700') in column "version"
 * to the fully qualified classname as expected for doctrine/migrate v3
 * (`Vendor\Site\Migrations\Mysql\Version20230912174700`)
 *
 * Requires the database:updateschema to have been aplied before (increases column size)!
 */
class StatusTableUpgradeWizard implements UpgradeWizardInterface
{
    private string $tableName = 'doctrine_migrationstatus';

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'migration_statustable';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return 'Update doctrine_migrationstatus table';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Update the migration table of doctrine/migration from v2 to v3';
    }

    /**
     * Gather a mapping of Version2023xxxxx numbers to fully qualified PHP classnames
     *
     * @return array<string, string>
     * @throws \Doctrine\DBAL\Exception
     */
    private function getVersionMapping(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $databasePlatformName = $connection->getDatabasePlatform()->getName();

        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $mapping = [];
        foreach ($packageManager->getActivePackages() as $package) {
            $autoloadComposerDefinition = $package->getValueFromComposerManifest('autoload');
            if (! ($autoloadComposerDefinition->{'psr-4'} ?? null) instanceof \stdClass) {
                continue;
            }
            $psr4Namespaces = get_object_vars($autoloadComposerDefinition->{'psr-4'});
            foreach ($psr4Namespaces as $namespace => $dir) {
                if (strpos($namespace, '\\Migrations')) {
                    $fullDir = $package->getPackagePath() . $dir . ucfirst($databasePlatformName);
                    $files = glob($fullDir . '/Version*.php');
                    if (! is_array($files)) {
                        continue;
                    }
                    foreach ($files as $file) {
                        $className = rtrim($namespace, '\\') . '\\' . ucfirst($databasePlatformName)
                            . '\\' . str_replace('.php', '', basename($file));
                        $legacyNumber = preg_replace('/[^0-9]/', '', basename($file));
                        $mapping[$legacyNumber] = $className;
                    }
                }
            }
        }
        return $mapping;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->tableName);
        return $connection->createQueryBuilder();
    }

    /**
     * Loop through all available migration classes and update the status table accordingly
     *
     * @inheritDoc
     */
    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getQueryBuilder();
        foreach ($this->getVersionMapping() as $version => $className) {
            $queryBuilder->update($this->tableName)
                ->set('version', $className, true, Connection::PARAM_STR)
                ->where(
                    $queryBuilder->expr()->eq(
                        'version',
                        $queryBuilder->createNamedParameter($version, Connection::PARAM_STR)
                    )
                );
            $queryBuilder->execute();
        }

        return true;
    }

    /**
     * Check if there is any version stored in the status table which does not contain a "\"
     *
     * @inheritDoc
     */
    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getQueryBuilder();
        $count = (int)$queryBuilder->count('*')
            ->from($this->tableName)
            ->where($queryBuilder->expr()->notLike(
                'version',
                $queryBuilder->createNamedParameter('%\\\%', Connection::PARAM_STR)
            ))
            ->executeQuery()
            ->fetchOne();
        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }
}
