<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Marein\LockDoctrineMigrationsBundle\MareinLockDoctrineMigrationsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private string $uniqueId;

    /**
     * @var array<string, mixed>
     */
    private array $bundleConfiguration;

    /**
     * @param array<string, mixed> $bundleConfiguration
     */
    public function __construct(array $bundleConfiguration)
    {
        parent::__construct('prod', false);

        $this->uniqueId = uniqid();
        $this->bundleConfiguration = $bundleConfiguration;
    }

    public function getCacheDir(): string
    {
        return '/tmp/' . $this->uniqueId . '/cache';
    }

    public function getLogDir(): string
    {
        return '/tmp/' . $this->uniqueId . '/log';
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MareinLockDoctrineMigrationsBundle(),
            new DoctrineMigrationsBundle(),
            new DoctrineBundle()
        ];
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator)
    {
        $containerConfigurator->extension(
            'marein_lock_doctrine_migrations',
            $this->bundleConfiguration
        );

        $containerConfigurator->extension(
            'doctrine',
            [
                'dbal' => [
                    'connections' => [
                        'mysql' => [
                            'url' => 'mysql://root:password@127.0.0.1:3306/db',
                            'wrapper_class' => ConnectionTestDouble::class
                        ],
                        'pgsql' => [
                            'url' => 'pgsql://postgres:password@127.0.0.1:5432/db',
                            'wrapper_class' => ConnectionTestDouble::class
                        ]
                    ]
                ]
            ]
        );

        $containerConfigurator->extension(
            'doctrine_migrations',
            [
                'migrations' => [
                    Migration::class
                ]
            ]
        );

        // The bundle uses Doctrine\Migrations\Configuration\Connection\ExistingConnection by default.
        // Although the connection is configurable via a separate config file or cli argument,
        // this will not work because the ExistingConnection behaviour prevents overriding.
        $containerConfigurator->services()
            ->set('app.connection_loader', ConnectionRegistryConnection::class)
            ->decorate('doctrine.migrations.connection_loader')
            ->factory([ConnectionRegistryConnection::class, 'withSimpleDefault'])
            ->args([new Reference('doctrine')]);
    }
}
