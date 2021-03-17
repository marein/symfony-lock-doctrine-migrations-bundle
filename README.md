# symfony-lock-doctrine-migrations-bundle

![CI](https://github.com/marein/symfony-lock-doctrine-migrations-bundle/workflows/CI/badge.svg?branch=master)

__Table of contents__

* [Overview](#overview)
  * [How it works?](#how-it-works)
* [Installation and requirements](#installation-and-requirements)
* [Configuration](#configuration)
* [Public api](#public-api)

## Overview

Perform concurrent doctrine migrations safely.

### How it works?

It hooks into Symfony's event system and listens for the `doctrine:migrations:migrate` command to be executed.
The command must be executed with the `--conn` option so that this bundle knows which connection to use.
If the platform of the selected connection is supported, the operation is performed inside a distributed lock.

Supported platforms:
* MySQL
* PostgreSQL

## Installation and requirements

Add the bundle to your project.

```
composer require marein/symfony-lock-doctrine-migrations-bundle
```

Add the bundle in the kernel. This can be different for your setup.

```php
public function registerBundles()
{
    return [
        // ...
        new \Marein\LockDoctrineMigrationsBundle\MareinLockDoctrineMigrationsBundle(),
        // ...
    ];
}
```

## Configuration

This is an example of all configurations in yaml format.

```yaml
marein_lock_doctrine_migrations:
    # Define a prefix for the name of the lock.
    #
    # Type: string
    # Default: migrate__
    lock_name_prefix: custom__
```

## Public api

Only the bundle configuration is part of the public api. Everything else can change and
is not considered a breaking change. Please don't use classes or services directly.
