<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle;

use Marein\LockDoctrineMigrationsBundle\EventListener\LockMigrationsListener;
use Marein\LockDoctrineMigrationsBundle\Platform\Platforms;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(
            'marein_lock_doctrine_migrations.event_listener.lock_migrations_listener',
            LockMigrationsListener::class
        )
        ->args(
            [
                service('marein_lock_doctrine_migrations.platform.platforms'),
                null
            ]
        )
        ->tag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND])
        ->tag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE]);

    $container->services()
        ->set(
            'marein_lock_doctrine_migrations.platform.platforms',
            Platforms::class
        )
        ->args(
            [
                service('doctrine')
            ]
        );
};
