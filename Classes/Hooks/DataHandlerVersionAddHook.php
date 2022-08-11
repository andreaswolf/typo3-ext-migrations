<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Hooks;

use KayStrobach\Migrations\Service\DoctrineMigrationCoordinator;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerVersionAddHook
{

    /**
     * @param array<string, mixed> $fieldArray
     * @param string|int $id The record ID, either a string "NEW..." or the existing record's UID
     */
    public function processDatamap_postProcessFieldArray(
        string $status,
        string $table,
        $id,
        array &$fieldArray,
        DataHandler $dataHandler
    ): void {
        if (!isset($GLOBALS['TCA'][$table])) {
            return;
        }

        $migrationCoordinator = GeneralUtility::makeInstance(DoctrineMigrationCoordinator::class);

        if ($status === 'new' && $migrationCoordinator->isVersioningEnabledForTable($table) && $migrationCoordinator->isMigrationBeingExecuted()) {
            $fieldArray['tx_migrations_version'] = $migrationCoordinator->getCurrentVersion();
        }
    }
}
