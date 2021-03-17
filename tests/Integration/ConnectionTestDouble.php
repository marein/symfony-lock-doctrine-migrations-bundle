<?php
declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DbalConnection;

final class ConnectionTestDouble extends DbalConnection
{
    /**
     * @var array<int, mixed>
     */
    public array $executedQueryCalls = [];

    public function connect()
    {
        try {
            parent::connect();
        } catch (\Throwable $e) {
            sleep(1);
            $this->connect();
        }
    }

    public function executeQuery($sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null)
    {
        $this->executedQueryCalls[] = [$sql, $params];

        return parent::executeQuery($sql, $params, $types, $qcp);
    }
}
