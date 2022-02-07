<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Service;

use TYPO3\CMS\Core\SingletonInterface;

class DoctrineMigrationCoordinator implements SingletonInterface
{

    /**
     * @var ?string
     */
    private $currentVersion = null;


    public function setCurrentVersion(string $version)
    {
        $this->currentVersion = $version;
    }

    public function resetCurrentVersion()
    {
        $this->currentVersion = null;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function isMigrationBeingExecuted()
    {
        return $this->currentVersion !== null;
    }

    public function isVersioningEnabledForTable(string $table): bool
    {
        return isset($GLOBALS['TCA'][$table]['columns']['tx_migrations_version']);
    }

}
