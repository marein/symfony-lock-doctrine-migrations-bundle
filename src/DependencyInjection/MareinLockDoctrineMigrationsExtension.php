<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class MareinLockDoctrineMigrationsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__) . '/Resources/config')
        );
        $loader->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->getDefinition('marein_lock_doctrine_migrations.event_listener.lock_migrations_listener')
            ->replaceArgument(1, $config['lock_name_prefix']);
    }
}
