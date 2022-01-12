<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\DBAL\Connection as DbalConnection;
use Throwable;

final class ConnectionTestDouble extends DbalConnection
{
    public function connect()
    {
        try {
            parent::connect();
        } catch (Throwable $e) {
            sleep(1);
            $this->connect();
        }
    }
}
