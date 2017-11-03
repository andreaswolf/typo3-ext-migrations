<?php

namespace KayStrobach\Migrations\Service;


use Doctrine\DBAL\Migrations\Version;
use Symfony\Component\Console\Formatter\OutputFormatter;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\Flow\Utility\Files;

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
     * @var \Doctrine\DBAL\Configuration
     */
    protected $config;

    /**
     * DoctrineService constructor.
     */
    public function __construct()
    {
        $this->packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Package\PackageManager');
        $this->config = new \Doctrine\DBAL\Configuration();
    }

    /**
     * Return the configuration needed for Migrations.
     *
     * @return \Doctrine\DBAL\Migrations\Configuration\Configuration
     */
    protected function getMigrationConfiguration()
    {
        $this->output = array();
        $that = $this;
        $outputWriter = new \Doctrine\DBAL\Migrations\OutputWriter(
            function ($message) use ($that) {
                $outputFormatter = new OutputFormatter(true);
                echo $outputFormatter->format($message);
                $that->output[] = $message;
            }
        );

        $connection = \Doctrine\DBAL\DriverManager::getConnection(
            array(
                'dbname'   => $GLOBALS['TYPO3_CONF_VARS']['DB']['database'],
                'user'     => $GLOBALS['TYPO3_CONF_VARS']['DB']['username'],
                'password' => $GLOBALS['TYPO3_CONF_VARS']['DB']['password'],
                'host'     => $GLOBALS['TYPO3_CONF_VARS']['DB']['host'],
                'driver'   => 'pdo_mysql',
            )
        );

        $configuration = new \Doctrine\DBAL\Migrations\Configuration\Configuration($connection, $outputWriter);
        $configuration->setMigrationsNamespace('KayStrobach\Migrations\Persistence\Doctrine\Migrations');
        Files::createDirectoryRecursively(PATH_site . '/fileadmin/Migrations');
        $configuration->setMigrationsDirectory(PATH_site . '/fileadmin/Migrations');
        $configuration->setMigrationsTableName('doctrine_migrationstatus');

        $configuration->createMigrationTable();

        $databasePlatformName = 'Mysql';
        foreach ($this->packageManager->getActivePackages() as $package) {
            $path = Files::concatenatePaths(array(
                $package->getPackagePath(),
                'Migrations',
                $databasePlatformName
            ));
            if (is_dir($path)) {
                $configuration->registerMigrationsFromDirectory($path);
            }
        }
        return $configuration;
    }

    /**
     * Returns the current migration status formatted as plain text.
     *
     * @return string
     */
    public function getMigrationStatus()
    {
        $configuration = $this->getMigrationConfiguration();

        $currentVersion = $configuration->getCurrentVersion();
        if ($currentVersion) {
            $currentVersionFormatted = $configuration->formatVersion($currentVersion) . ' (' . $currentVersion . ')';
        } else {
            $currentVersionFormatted = 0;
        }
        $latestVersion = $configuration->getLatestVersion();
        if ($latestVersion) {
            $latestVersionFormatted = $configuration->formatVersion($latestVersion) . ' (' . $latestVersion . ')';
        } else {
            $latestVersionFormatted = 0;
        }
        $executedMigrations = $configuration->getNumberOfExecutedMigrations();
        $availableMigrations = $configuration->getNumberOfAvailableMigrations();
        $newMigrations = $availableMigrations - $executedMigrations;

        $output = "\n == Configuration\n";

        $info = array(
            'Name'                  => $configuration->getName() ? $configuration->getName() : 'Doctrine Database Migrations',
            'Database Driver'       => $configuration->getConnection()->getDriver()->getName(),
            'Database Name'         => $configuration->getConnection()->getDatabase(),
            'Configuration Source'  => $configuration instanceof \Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration ? $configuration->getFile() : 'manually configured',
            'Version Table Name'    => $configuration->getMigrationsTableName(),
            'Migrations Namespace'  => $configuration->getMigrationsNamespace(),
            'Migrations Target Directory'  => $configuration->getMigrationsDirectory(),
            'Current Version'       => $currentVersionFormatted,
            'Latest Version'        => $latestVersionFormatted,
            'Available Migrations'  => $availableMigrations,
            'Executed Migrations'   => $executedMigrations,
            'New Migrations'        => $newMigrations
        );
        foreach ($info as $name => $value) {
            $output .= '    >> ' . $name . ': ' . str_repeat(' ', 50 - strlen($name)) . $value . PHP_EOL;
        }

        if ($migrations = $configuration->getMigrations()) {
            $output .= "\n == Migration Versions\n";
            foreach ($migrations as $version) {
                $packageKey = $this->getPackageKeyFromMigrationVersion($version);
                $croppedPackageKey = strlen($packageKey) < 24 ? $packageKey : substr($packageKey, 0, 23) . '~';
                $packageKeyColumn = ' ' . str_pad($croppedPackageKey, 24, ' ');
                $status = $version->isMigrated() ? 'migrated' : 'not migrated';
                $output .= '    >> ' . $configuration->formatVersion($version->getVersion()) . ' (' . $version->getVersion() . ')' . $packageKeyColumn . str_repeat(' ', 4) . $status . PHP_EOL;
                if ($version->getMigration()->getDescription() !== '') {
                    $output .= '       ' . $version->getMigration()->getDescription() . PHP_EOL;
                }
            }
        }

        return $output;
    }

    /**
     * Tries to find out a package key which the Version belongs to. If no
     * package could be found, an empty string is returned.
     *
     * @param Version $version
     * @return string
     */
    protected function getPackageKeyFromMigrationVersion(Version $version)
    {
        $sortedAvailablePackages = $this->packageManager->getAvailablePackages();
        usort($sortedAvailablePackages, function (PackageInterface $packageOne, PackageInterface $packageTwo) {
            return strlen($packageTwo->getPackagePath()) - strlen($packageOne->getPackagePath());
        });

        $reflectedClass = new \ReflectionClass($version->getMigration());
        $classPathAndFilename = Files::getUnixStylePath($reflectedClass->getFileName());

        /** @var $package PackageInterface */
        foreach ($sortedAvailablePackages as $package) {
            $packagePath = Files::getUnixStylePath($package->getPackagePath());
            if (strpos($classPathAndFilename, $packagePath) === 0) {
                return $package->getPackageKey();
            }
        }

        return '';
    }

    /**
     * Execute all new migrations, up to $version if given.
     *
     * If $outputPathAndFilename is given, the SQL statements will be written to the given file instead of executed.
     *
     * @param string $version The version to migrate to
     * @param string $outputPathAndFilename A file to write SQL to, instead of executing it
     * @param boolean $dryRun Whether to do a dry run or not
     * @param boolean $quiet Whether to do a quiet run or not
     * @return string
     */
    public function executeMigrations($version = null, $outputPathAndFilename = null, $dryRun = false, $quiet = false)
    {
        $configuration = $this->getMigrationConfiguration();
        $migration = new \Doctrine\DBAL\Migrations\Migration($configuration);

        if ($outputPathAndFilename !== null) {
            $migration->writeSqlFile($outputPathAndFilename, $version);
        } else {
            $migration->migrate($version, $dryRun);
        }

        if ($quiet === true) {
            $output = '';
            foreach ($this->output as $line) {
                $line = strip_tags($line);
                if (strpos($line, '  ++ migrating ') !== false || strpos($line, '  -- reverting ') !== false) {
                    $output .= substr($line, -15);
                }
            }
            return $output;
        } else {
            return strip_tags(implode(PHP_EOL, $this->output));
        }
    }
}