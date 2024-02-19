<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\EventListener;

use Marein\LockDoctrineMigrationsBundle\Platform\PlatformException;
use Marein\LockDoctrineMigrationsBundle\Platform\Platforms;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

final class LockMigrationsListener
{
    public function __construct(
        private Platforms $platforms,
        private string $lockNamePrefix
    ) {
    }

    /**
     * @throws PlatformException
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $connectionName = $this->getConnectionName($event);

        $this->platforms
            ->fromConnectionName($connectionName)
            ->acquireLock($this->getLockName($connectionName));
    }

    /**
     * @throws PlatformException
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $connectionName = $this->getConnectionName($event);

        $this->platforms
            ->fromConnectionName($connectionName)
            ->releaseLock($this->getLockName($connectionName));
    }

    private function shouldSkip(ConsoleEvent $event): bool
    {
        return $event->getCommand() === null
            || (string)$event->getCommand()->getName() !== 'doctrine:migrations:migrate'
            || $this->getConnectionName($event) === '';
    }

    private function getConnectionName(ConsoleEvent $event): string
    {
        return (string)$event->getInput()->getOption('conn');
    }

    private function getLockName(string $connectionName): string
    {
        return $this->lockNamePrefix . $connectionName;
    }
}
