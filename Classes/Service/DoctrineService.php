<?php
declare(strict_types = 1);

namespace KayStrobach\Migrations\Service;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class DoctrineService implements LoggerAwareInterface
{
    private const MIGRATION_TABLE_NAME = 'doctrine_migrationstatus';

    use LoggerAwareTrait;

    /** @var mixed[] */
    protected array $output;

    protected PackageManager $packageManager;

    protected ConnectionPool $connectionPool;

    protected LogManager $logManager;

    /** @var bool */
    private bool $dryRun = false;

    /**
     * DoctrineService constructor.
     */
    public function __construct(
        ?PackageManager $packageManager = null,
        ?ConnectionPool $connectionPool = null,
        ?LogManager $logManager = null
    ) {
        $this->packageManager = $packageManager ?? $this->getPackageManager();
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $logManager = $logManager ?? GeneralUtility::makeInstance(LogManager::class);
        $this->setLogger($logManager->getLogger(static::class));
    }

    /**
     * Return the configuration needed for Migrations.
     */
    public function getMigrationConfiguration(string $connectionName, string $filterForPackageKey = null): Configuration
    {
        $connection = $this->connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);

        $configuration = new Configuration(
            $connection,
            $this->getOutputWriter()
        );

        $configuration->setMigrationsNamespace('KayStrobach\Migrations\Persistence\Doctrine\Migrations');
        $publicPath = Environment::getPublicPath();

        GeneralUtility::mkdir_deep($publicPath . '/fileadmin/Migrations');
        $configuration->setMigrationsDirectory($publicPath . '/fileadmin/Migrations');

        $configuration->setMigrationsTableName(self::MIGRATION_TABLE_NAME);

        $configuration->createMigrationTable();

        $databasePlatformName = $connection->getDatabasePlatform()->getName();
        foreach ($this->packageManager->getActivePackages() as $package) {
            if ($filterForPackageKey !== null && $package->getPackageKey() !== $filterForPackageKey) {
                continue;
            }

            [$namespace, $path] = $this->getPackageMigrationNamespaceAndDirectory($package);

            if ($namespace === null || $path === null) {
                $this->debug(sprintf('Package %s does not contain any migrations', $package->getPackageKey()));
                continue;
            }

            $plattformPath = $path . ucfirst($databasePlatformName) . '/';

            if (is_dir($path)) {
                $configuration->setMigrationsNamespace($namespace);

                $configuration->registerMigrationsFromDirectory($path);
            }

            if (is_dir($plattformPath)) {
                $namespace .= ucfirst($databasePlatformName);
                    $configuration->setMigrationsNamespace($namespace);
                    $configuration->registerMigrationsFromDirectory($plattformPath);
                if ($filterForPackageKey !== null) {
                    $configuration->setMigrationsDirectory($plattformPath);
                }
            } else if ($connectionName === 'Default') {
                $configuration->setMigrationsNamespace($namespace);
                $configuration->registerMigrationsFromDirectory($path);

                if ($filterForPackageKey !== null) {
                    $configuration->setMigrationsDirectory($path);
                }
            }
        }
        return $configuration;
    }

    private function getOutputWriter(): OutputWriter
    {
        $this->output = [];
        return new OutputWriter(
            function (string $message) {
                $outputFormatter = new OutputFormatter(true);
                echo $outputFormatter->format($message);
                $this->debug($message);
                $this->output[] = $message;
            }
        );
    }

    /**
     * @param PackageInterface $package
     * @return array<mixed> [The namespace, the full package directory]
     */
    private function getPackageMigrationNamespaceAndDirectory(PackageInterface $package): array
    {
        $autoloadComposerDefinition = $package->getValueFromComposerManifest('autoload');

        if ($autoloadComposerDefinition->{'psr-4'} instanceof \stdClass) {
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

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function debug(string $message): void
    {
        if ($this->getLogger()) {
            $this->getLogger()->debug($message);
        }
    }

    protected function getPackageManager(): PackageManager
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < 100000) {
            $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
            $packageManager = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService);
        } else {
            $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        }

        return $packageManager;
    }
}
