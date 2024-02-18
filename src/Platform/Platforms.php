<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Exception;

final class Platforms
{
    public function __construct(
        private Registry $doctrine
    ) {
    }

    /**
     * @throws PlatformException
     */
    public function fromConnectionName(string $name): Platform
    {
        $connection = $this->getConnection($name);

        try {
            $databasePlatform = $connection->getDatabasePlatform();
        } catch (Exception $e) {
            throw new PlatformException($e->getMessage(), $e->getCode(), $e);
        }

        $databasePlatformName = '';
        if (method_exists($databasePlatform, 'getName')) {
            $databasePlatformName = $databasePlatform->getName();
        }

        return match (true) {
            $databasePlatform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform,
            $databasePlatformName === 'mysql' => new MysqlPlatform($connection),
            $databasePlatform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform,
            $databasePlatformName === 'postgresql' => new PostgresqlPlatform($connection),
            default => new NullPlatform()
        };
    }

    private function getConnection(string $connectionName): Connection
    {
        $connection = $this->doctrine->getConnection($connectionName);
        assert($connection instanceof Connection);

        return $connection;
    }
}
