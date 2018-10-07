Doctrine Fixtures Extension for Behat
=====================================

The extension increases feature test isolation by reloading ORM data fixtures between scenarios and features.

# Installation

```sh
composer require "behat-extension/doctrine-data-fixtures-extension"
```

# Configuration

Activate extension in your **behat.yml** and define any fixtures to be loaded:

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime:    'feature'
      directories: ~
      fixtures:    ~
```

When **lifetime** is set to "feature" (or unspecified), data fixtures are reloaded between feature files.  Alternately,
when **lifetime** is set to "scenario", data fixtures are reloaded between scenarios (i.e., increased
test isolation at the expense of increased run time).

When **fixtures** is set, the extension will load the specified fixture classes.
This must contain a list of fully qualified class names. Classes MUST not have any constructor arguments (or at least optional).
If the interface `Symfony\Component\DependencyInjection\ContainerAwareInterface` is implemented, the container is set to the fixture loader.

When **directories** is set, the extension will load the data fixtures globed from the respective directories.
Classes MUST not have any constructor arguments (or at least optional).
If the interface `Symfony\Component\DependencyInjection\ContainerAwareInterface` is implemented, the container is set to the fixture loader.

This extension will also load every fixtures declared as services and tagged with `doctrine.fixture.orm`.

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime: 'feature'
      directories:
        - '/project/src/AcmeAnalytics/Tests/DataFixtures/ORM'
      fixtures:
        - 'Acme\StoreBundle\DataFixture\ORM\Categories'
        - 'Acme\StoreBundle\DataFixture\ORM\Apps'
        - 'Acme\VendorBundle\DataFixture\ORM\Vendors'
```

# Backup System

To speed up the tests, a backup system is available. The whole database will be set in cache and reloaded when needed.
You should periodically clear the cache as it does not detect changes to the data fixture contents because the hash is based on the collection of data fixture class names.

This feature is only available for SQLite, MySQL and PostgreSQL.

* For MySQL, `mysql` and `mysqldump` must be available.
* For PostgreSQL, `pg_restore` and `pg_dump` must be available.

It is enabled by default. To disable it, you just have to set `use_backup: false` in the extension configuration:

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime: 'feature'
      use_backup: false
```

# Source

Github: [https://github.com/BehatExtension/DoctrineDataFixturesExtension](https://github.com/BehatExtension/DoctrineDataFixturesExtension)

Forked from [https://github.com/vipsoft/DoctrineDataFixturesExtension](https://github.com/vipsoft/DoctrineDataFixturesExtension)

# Copyright

* Copyright (c) 2012 Anthon Pang.
* Copyright (c) 2016-2018 Florent Morselli.

See [LICENSE](LICENSE) for details.

# Contributors

* Anthon Pang ([robocoder](http://github.com/robocoder))
* Florent Morselli ([Spomky](http://github.com/Spomky))
* [Others contributors](https://github.com/BehatExtension/DoctrineDataFixturesExtension/graphs/contributors)
