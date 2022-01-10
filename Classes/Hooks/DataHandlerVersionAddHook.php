<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Hooks;

use AndreasWolf\Migrations\Service\DoctrineMigrationCoordinator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerVersionAddHook
{
    /**
     *
     * @param object $fObj TCEmain object reference
     * @param string $status The status, 'new' or 'update'
     * @param string $table
     * @param string|int $id The record ID, either a string "NEW..." or the existing record's UID
     *
     * @return void
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$incomingFieldArray, &$fObj)
    {
        if (!isset($GLOBALS['TCA'][$table])) {
            return;
        }

        $migrationCoordinator = GeneralUtility::makeInstance(DoctrineMigrationCoordinator::class);

        if ($status === 'new' && $migrationCoordinator->isVersioningEnabledForTable($table) && $migrationCoordinator->isMigrationBeingExecuted()) {
            $incomingFieldArray['tx_migrations_version'] = $migrationCoordinator->getCurrentVersion();
        }
    }
}
