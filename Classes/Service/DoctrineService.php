<?php

namespace KayStrobach\Migrations\Service;


use Symfony\Component\Console\Formatter\OutputFormatter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class DoctrineService implements SingletonInterface
{
    /**
     * @var
     */
    protected $output;

    /**
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected $packageManager;

    /** @var bool */
    private $dryRun = false;

    /**
     * DoctrineService constructor.
     */
    public function __construct()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < 100000) {
            $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
            $this->packageManager = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService);
        } else {
            $this->packageManager = GeneralUtility::makeInstance(PackageManager::class);
        }
    }

    /**
     * Return the configuration needed for Migrations.
     *
     * @param string $connectionName The connection name
     * @return \Doctrine\Migrations\Configuration\Configuration
     */
    public function getMigrationConfiguration(string $connectionName)
    {
        $this->output = array();
        $that = $this;
        $outputWriter = new \Doctrine\Migrations\OutputWriter(
            function ($message) use ($that) {
                $outputFormatter = new OutputFormatter(true);
                echo $outputFormatter->format($message);
                $that->output[] = $message;
            }
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName($connectionName);

        $configuration = new \Doctrine\Migrations\Configuration\Configuration($connection, $outputWriter);
        $configuration->setMigrationsNamespace('KayStrobach\Migrations\Persistence\Doctrine\Migrations');
        $publicPath = class_exists('\\TYPO3\\CMS\\Core\\Core\\Environment') ? \TYPO3\CMS\Core\Core\Environment::getPublicPath() : PATH_site;
        GeneralUtility::mkdir_deep($publicPath . '/fileadmin/Migrations');
        $configuration->setMigrationsDirectory($publicPath . '/fileadmin/Migrations');
        $configuration->setMigrationsTableName('doctrine_migrationstatus');

        $configuration->createMigrationTable();

        $databasePlatformName = $connection->getDatabasePlatform()->getName();
        foreach ($this->packageManager->getActivePackages() as $package) {
            [$namespace, $path] = $this->getPackageMigrationNamespaceAndDirectory($package);

            if ($namespace === null) {
                // TODO log
                continue;
            }

            $path .= ucfirst($databasePlatformName) . '/';
            // unlike in Composer manifests, the search namespace here must _not_ end with a trailing slash!
            $namespace .= ucfirst($databasePlatformName);
            if (is_dir($path)) {
                $configuration->setMigrationsNamespace($namespace);

                $configuration->registerMigrationsFromDirectory($path);
            }
        }
        return $configuration;
    }


    /**
     * @param PackageInterface $package
     * @return array [The namespace, the full package directory]
     */
    private function getPackageMigrationNamespaceAndDirectory(PackageInterface $package)
    {
        $autoloadComposerDefinition = $package->getValueFromComposerManifest('autoload');

        if ($autoloadComposerDefinition->{'psr-4'} instanceof \stdClass) {
            $psr4Namespaces = get_object_vars($autoloadComposerDefinition->{'psr-4'});
            foreach ($psr4Namespaces as $namespace => $dir) {
                if (substr($namespace, -12) === '\\Migrations\\') {
                    $fullDir = $package->getPackagePath() . $dir . '/';

                    return [$namespace, $fullDir];
                }
            }
        }

        foreach ($autoloadComposerDefinition->{'psr-0'} ?? [] as $namespace => $dir) {
            if (substr($namespace, -12) === '\\Migrations\\') {
                $fullDir = $package->getPackagePath() . $dir . '/';

                return [$namespace, $fullDir];
            }
        }

        return [null, null];
    }

    public function setDryRun(bool $dryRun)
    {
        $this->dryRun = $dryRun;
    }

    public function isDryRun()
    {
        return $this->dryRun;
    }
}
