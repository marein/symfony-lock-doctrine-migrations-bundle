<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Exception;

final class Platforms
{
    private Registry $doctrine;

    /**
     * @var array<string, callable>
     */
    private array $platformFactories;

    /**
     * @var callable
     */
    private $fallbackPlatformFactory;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->platformFactories = [
            'mysql' => fn(Connection $connection) => new MysqlPlatform($connection),
            'postgresql' => fn(Connection $connection) => new PostgresqlPlatform($connection)
        ];
        $this->fallbackPlatformFactory = fn(Connection $connection) => new NullPlatform();
    }

    /**
     * @throws PlatformException
     */
    public function fromConnectionName(string $name): Platform
    {
        $connection = $this->getConnection($name);

        try {
            $platformName = $connection->getDatabasePlatform()->getName();

            return ($this->platformFactories[$platformName] ?? $this->fallbackPlatformFactory)($connection);
        } catch (Exception $e) {
            throw new PlatformException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function getConnection(string $connectionName): Connection
    {
        $connection = $this->doctrine->getConnection($connectionName);
        assert($connection instanceof Connection);

        return $connection;
    }
}
