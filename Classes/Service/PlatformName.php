<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\Service;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;

final class PlatformName
{
    /**
     * Required for doctrine/dbal 4+
     *
     * @see https://github.com/doctrine/dbal/pull/4763/files
     */
    public static function getNameForPlatform(AbstractPlatform $databasePlatform): string
    {
        if ($databasePlatform instanceof AbstractMySQLPlatform) {
            return 'mysql';
        }
        if ($databasePlatform instanceof OraclePlatform) {
            return 'oracle';
        }
        if ($databasePlatform instanceof PostgreSQLPlatform) {
            return 'postgresql';
        }
        if (class_exists(SQLitePlatform::class) && $databasePlatform instanceof SQLitePlatform) {
            return 'sqlite';
        }
        if (class_exists(SqlitePlatform::class) && $databasePlatform instanceof SqlitePlatform) {
            return 'sqlite';
        }
        throw new \RuntimeException(sprintf('Unsupported platform %s', get_class($databasePlatform)), 1732200673);
    }
}
