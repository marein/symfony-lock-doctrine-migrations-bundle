<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

interface Platform
{
    /**
     * @throws PlatformException
     */
    public function acquireLock(string $name): void;

    /**
     * @throws PlatformException
     */
    public function releaseLock(string $name): void;
}
