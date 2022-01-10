<?php

declare(strict_types=1);

namespace AndreasWolf\Migrations\Service;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Console\Formatter\OutputFormatter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DoctrineService implements LoggerAwareInterface
{
    private const MIGRATION_TABLENAME = 'doctrine_migrationstatus';
    use LoggerAwareTrait;

    /** @var string[]  */
    protected array $output;
    protected PackageManager $packageManager;
    private bool $dryRun = false;
    private ConnectionPool $connectionPool;

    public function __construct(PackageManager $packageManager, ConnectionPool $connectionPool, LogManager $logger)
    {
        $this->packageManager = $packageManager;
        $this->connectionPool = $connectionPool;
        $this->setLogger($logger->getLogger(static::class));
    }

    public function getMigrationConfiguration(string $connectionName, string $filterForPackageKey = null): Configuration
    {
        $connection = $this->connectionPool->getConnectionByName($connectionName);

        $configuration = new Configuration($connection, $this->getOutputWriter());
        $configuration->setMigrationsNamespace('AndreasWolf\Migrations');
        $publicPath = Environment::getPublicPath();
        GeneralUtility::mkdir_deep($publicPath . '/fileadmin/Migrations');
        $configuration->setMigrationsDirectory($publicPath . '/fileadmin/Migrations');
        $configuration->setMigrationsTableName(self::MIGRATION_TABLENAME);

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
     * @return string[] [The namespace, the full package directory]
     * @phpstan-return array{null|string, null|string}
     */
    private function getPackageMigrationNamespaceAndDirectory(PackageInterface $package): array
    {
        $autoloadComposerDefinition = $package->getValueFromComposerManifest('autoload');

        if ($autoloadComposerDefinition->{'psr-4'} instanceof stdClass) {
            $psr4Namespaces = get_object_vars($autoloadComposerDefinition->{'psr-4'});
            foreach ($psr4Namespaces as $namespace => $dir) {
                if (str_ends_with($namespace, '\\Migrations\\')) {
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
}
