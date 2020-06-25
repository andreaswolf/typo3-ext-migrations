<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Tests\Functional\Command;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use KayStrobach\Migrations\Typo3ConfigurationLoader;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \Doctrine\Migrations\Tools\Console\Command\MigrateCommand
 */
class MigrateCommandTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/migrations',
        'typo3conf/ext/migrations/Tests/Functional/Command/Fixtures/test_migrations',
    ];

    /** @test */
    public function migrationCommandExecutesMigrationsDefinedInExtensionWhenMigrationNamespaceIsRegisteredInComposer(): void
    {
        $migrateCommand = $this->get(MigrateCommand::class);
        $migrateCommand->setHelperSet(new HelperSet([
            'question' => new QuestionHelper(),
        ]));
        $commandTester = new CommandTester($migrateCommand);

        $commandTester->execute(['--no-interaction']);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Executing KayStrobach\\Migrations\\TestFixtures\\Migrations\\Mysql\\Version20230804102700', $output);

        /** @var \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = $this->get(ConnectionPool::class)
            ->getConnectionForTable(Typo3ConfigurationLoader::MIGRATION_TABLE_NAME);
        $result = $connection->select(['*'], Typo3ConfigurationLoader::MIGRATION_TABLE_NAME)->fetchAllAssociative();

        self::assertCount(1, $result);
        self::assertSame('KayStrobach\\Migrations\\TestFixtures\\Migrations\\Mysql\\Version20230804102700', $result[0]['version']);

        $result = $connection->select(['*'], 'pages', ['uid' => 1])->fetchAllAssociative();
        self::assertSame('My test page', $result[0]['title']);
    }
}
