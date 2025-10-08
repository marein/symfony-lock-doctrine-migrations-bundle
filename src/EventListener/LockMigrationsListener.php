<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\EventListener;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Exception\MissingDependency;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marein\LockDoctrineMigrationsBundle\Platform\PlatformException;
use Marein\LockDoctrineMigrationsBundle\Platform\Platforms;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

final class LockMigrationsListener
{
    public function __construct(
        private Platforms $platforms,
        private string $lockNamePrefix,
        private ManagerRegistry $registry,
        private DependencyFactory $dependencyFactory,
        private ?string $doctrineMigrationsPreferredConnection = null
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
        $connectionName = (string) $event->getInput()->getOption('conn');

        if ('' === $connectionName) {
            try {
                $entityManager = $this->dependencyFactory->getEntityManager();
            } catch (MissingDependency $e) {
                if ($e->getMessage() !== MissingDependency::noEntityManager()->getMessage()) {
                    throw $e;
                }

                $entityManager = null;
            }

            $connectionName = $entityManager
                ? $this->getEntityManagerConnectionName($entityManager)
                : null;

            return $connectionName
                ?? $this->doctrineMigrationsPreferredConnection
                ?? $this->registry->getDefaultConnectionName();
        }

        return $connectionName;
    }

    private function getEntityManagerConnectionName(EntityManagerInterface $entityManager): ?string
    {
        $connectionName = $entityManager->getConnection();

        foreach ($this->registry->getConnections() as $name => $connection) {
            if ($connection !== $connectionName) {
                continue;
            }

            return $name;
        }

        return null;
    }

    private function getLockName(string $connectionName): string
    {
        return $this->lockNamePrefix . $connectionName;
    }
}
