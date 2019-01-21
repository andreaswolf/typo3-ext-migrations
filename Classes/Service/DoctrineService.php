<?php

namespace KayStrobach\Migrations\Service;


use Doctrine\Migrations\Version\Version;
use Symfony\Component\Console\Formatter\OutputFormatter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DoctrineService
{
    /**
     * @var
     */
    protected $output;

    /**
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected $packageManager;

    /**
     * DoctrineService constructor.
     */
    public function __construct()
    {
        $this->packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Package\PackageManager');
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
        GeneralUtility::mkdir_deep(PATH_site . '/fileadmin/Migrations');
        $configuration->setMigrationsDirectory(PATH_site . '/fileadmin/Migrations');
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
}
