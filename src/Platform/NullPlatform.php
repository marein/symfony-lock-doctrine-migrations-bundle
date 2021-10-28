<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Platform;

final class NullPlatform implements Platform
{
    public function acquireLock(string $name): void
    {
        // Do nothing.
    }

    public function releaseLock(string $name): void
    {
        // Do nothing.
    }
}
