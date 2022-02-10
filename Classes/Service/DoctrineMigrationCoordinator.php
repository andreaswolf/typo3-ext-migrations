<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Service;

use TYPO3\CMS\Core\SingletonInterface;

class DoctrineMigrationCoordinator implements SingletonInterface
{
    private ?string $currentVersion = null;


    public function setCurrentVersion(string $version): void
    {
        $this->currentVersion = $version;
    }

    public function resetCurrentVersion(): void
    {
        $this->currentVersion = null;
    }

    public function getCurrentVersion(): ?string
    {
        return $this->currentVersion;
    }

    public function isMigrationBeingExecuted(): bool
    {
        return $this->currentVersion !== null;
    }

    public function isVersioningEnabledForTable(string $table): bool
    {
        return isset($GLOBALS['TCA'][$table]['columns']['tx_migrations_version']);
    }

}
