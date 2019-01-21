<?php

namespace KayStrobach\Migrations\Command;

use KayStrobach\Migrations\Service\DoctrineService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

class DoctrineCommandController extends CommandController
{
    /**
     * @var DoctrineService
     */
    protected $doctrineService;

    /**
     * DoctrineCommandController constructor.
     */
    public function __construct()
    {
        $this->doctrineService = GeneralUtility::makeInstance(
            DoctrineService::class
        );
    }

    /**
     * create a migration template
     */
    public function createCommand() {

    }

    /**
     * list the migration status
     */
    public function statusCommand() {
        $this->outputLine(
            $this->doctrineService->getMigrationStatus()
        );

    }

    /**
     * Migrate the database schema
     *
     * Adjusts the database structure by applying the pending
     * migrations provided by currently active packages.
     *
     * @param string $version The version to migrate to
     * @param string $output A file to write SQL to, instead of executing it
     * @param boolean $dryRun Whether to do a dry run or not
     * @param boolean $quiet If set, only the executed migration versions will be output, one per line
     * @return void
     * @see typo3.flow:doctrine:migrationstatus
     * @see typo3.flow:doctrine:migrationexecute
     * @see typo3.flow:doctrine:migrationgenerate
     * @see typo3.flow:doctrine:migrationversion
     */
    public function migrateCommand($version = null, $output = null, $dryRun = false, $quiet = false) {
        $result = $this->doctrineService->executeMigrations($version, $output, $dryRun, $quiet);
        if ($result == '') {
            if (!$quiet) {
                $this->outputLine('No migration was necessary.');
            }
        } elseif ($output === null) {
            $this->outputLine($result);
        } else {
            if (!$quiet) {
                $this->outputLine('Wrote migration SQL to file "' . $output . '".');
            }
        }
    }
}