<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Tests\Functional\Migration;

use KayStrobach\Migrations\Service\DoctrineService;
use KayStrobach\Migrations\Tester\DoctrineCommandRunner;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \KayStrobach\Migrations\Migration\AbstractDataHandlerMigration
 */
class AbstractDataHandlerMigrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/migrations',
        'typo3conf/ext/migrations/Tests/Functional/Migration/Fixtures/test_migrations',
    ];

    /** @test */
    public function dataHandlerMigrationRunsDataHandler(): void
    {
        Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
        Bootstrap::initializeLanguageObject();
        $GLOBALS['BE_USER']->workspace = 0;
        $this->get(DoctrineCommandRunner::class)->executeMigrateCommand();

        $connection = $this->get(ConnectionPool::class)
            ->getConnectionForTable(DoctrineService::MIGRATION_TABLE_NAME);
        $result = $connection->select(['*'], DoctrineService::MIGRATION_TABLE_NAME)->fetchAllAssociative();

        self::assertCount(1, $result, 'No or more than one migration was executed');
        self::assertSame('20230804162200', $result[0]['version']);

        $result = BackendUtility::getRecord('pages', 1);
        self::assertIsArray($result);
        self::assertSame('My DataHandler test page', $result['title']);
    }
}
