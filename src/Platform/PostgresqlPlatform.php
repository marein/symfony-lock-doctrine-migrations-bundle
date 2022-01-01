<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

use Doctrine\DBAL\Connection;
use Exception;

final class PostgresqlPlatform implements Platform
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function acquireLock(string $name): void
    {
        try {
            $this->connection->executeQuery(
                'SELECT pg_advisory_lock(hashtext(?))',
                [$name]
            );
        } catch (Exception $e) {
            throw new PlatformException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function releaseLock(string $name): void
    {
        try {
            $this->connection->executeQuery(
                'SELECT pg_advisory_unlock(hashtext(?))',
                [$name]
            );
        } catch (Exception $e) {
            throw new PlatformException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
