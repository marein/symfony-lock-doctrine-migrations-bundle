<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

use Doctrine\DBAL\Connection;
use Exception;

final class MysqlPlatform implements Platform
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function acquireLock(string $name): void
    {
        try {
            $this->connection->executeQuery(
                'SELECT GET_LOCK(?, -1)',
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
                'SELECT RELEASE_LOCK(?)',
                [$name]
            );
        } catch (Exception $e) {
            throw new PlatformException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
