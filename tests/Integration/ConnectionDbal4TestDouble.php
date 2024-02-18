<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Result;
use Throwable;

final class ConnectionDbal4TestDouble extends DbalConnection
{
    public array $loggedQueries = [];

    public function connect(): Connection
    {
        try {
            return parent::connect();
        } catch (Throwable $e) {
            sleep(1);
            return $this->connect();
        }
    }

    public function executeQuery(string $sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $this->loggedQueries[] = [
            'sql' => $sql,
            'params' => $params
        ];

        return parent::executeQuery($sql, $params, $types, $qcp);
    }
}
