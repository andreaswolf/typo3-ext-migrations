<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Tests\Functional\Migration;

use KayStrobach\Migrations\Tester\DoctrineCommandRunner;
use KayStrobach\Migrations\Typo3ConfigurationLoader;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \KayStrobach\Migrations\Migration\AbstractDataHandlerMigration
 */
class AbstractDataHandlerMigrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'kaystrobach/migrations',
        'typo3conf/ext/migrations/Tests/Functional/Migration/Fixtures/test_migrations_datahandler',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('en_US');

        Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
        $GLOBALS['BE_USER']->workspace = 0;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dataHandlerMigrationRunsDataHandler(): void
    {
        $this->get(DoctrineCommandRunner::class)->executeMigrateCommand();

        /** @var \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = $this->get(ConnectionPool::class)
            ->getConnectionForTable(Typo3ConfigurationLoader::MIGRATION_TABLE_NAME);
        $result = $connection->select(['*'], Typo3ConfigurationLoader::MIGRATION_TABLE_NAME)->fetchAllAssociative();

        self::assertCount(1, $result, 'No or more than one migration was executed');
        self::assertSame(\KayStrobach\Migrations\TestFixtures\Migrations\Mysql\Version20230804162200::class, $result[0]['version']);

        $result = BackendUtility::getRecord('pages', 1);
        self::assertIsArray($result);
        self::assertSame('My DataHandler test page', $result['title']);
    }
}
