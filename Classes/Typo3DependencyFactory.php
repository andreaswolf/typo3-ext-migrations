<?php

namespace KayStrobach\Migrations;

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\DependencyFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;

/**
 * Implements the doctrine/migration DependencyFactory for TYPO3 specific purposes
 * - Gets the DBAL connection from the ConnectionPool
 * - Uses the Typo3ConfigurationLoader as our implementation for the ConfigurationLoader
 */
class Typo3DependencyFactory extends DependencyFactory
{

    public static function create(
        LogManager $logManager,
        ConnectionPool $connectionPool,
        Typo3ConfigurationLoader $configurationLoader
    ): DependencyFactory
    {
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $connectionLoader = new ExistingConnection($connection);

        return DependencyFactory::fromConnection(
            $configurationLoader,
            $connectionLoader,
            $logManager->getLogger()
        );
    }

}
