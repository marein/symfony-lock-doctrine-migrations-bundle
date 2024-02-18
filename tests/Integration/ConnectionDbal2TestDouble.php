<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DbalConnection;
use Throwable;

final class ConnectionDbal2TestDouble extends DbalConnection
{
    public array $loggedQueries = [];

    public function connect()
    {
        try {
            parent::connect();
        } catch (Throwable $e) {
            sleep(1);
            $this->connect();
        }
    }

    public function executeQuery($sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null)
    {
        $this->loggedQueries[] = [
            'sql' => $sql,
            'params' => $params
        ];

        return parent::executeQuery($sql, $params, $types, $qcp);
    }
}
