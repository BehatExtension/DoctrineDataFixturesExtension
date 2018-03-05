Doctrine Fixtures Extension for Behat
=====================================

The extension increases feature test isolation by reloading ORM data fixtures between scenarios and features.

# Installation

This extension requires:

* Behat 3.0+
* Mink 1.4+
* Doctrine ORM 2.x
* [Symfony2Extension](http://extensions.behat.org/symfony2/)

## Through Composer

```sh
composer require "behat-extension/doctrine-data-fixtures-extension"
```

#Configuration

Activate extension in your **behat.yml** and define any fixtures to be loaded:

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime:    'feature'
      autoload:    true
      directories: ~
      fixtures:    ~
```

When **lifetime** is set to "feature" (or unspecified), data fixtures are reloaded between feature files.  Alternately,
when **lifetime** is set to "scenario", data fixtures are reloaded between scenarios (i.e., increased
test isolation at the expense of increased run time).

When **autoload** is true, the extension will load the data fixtures for registered bundles.
Please note that only fixtures stored in the folder `/DataFixtures/ORM` of the bundles are loaded.
If you want to load fixtures tagged with `doctrine.fixture.orm`, you must enable the bundle `BehatExtension\DoctrineDataFixturesExtension\Bundle\BehatDoctrineDataFixturesExtensionBundle`
in your test `AppKernel` class.

When **fixtures** is set, the DoctrineDataFixtures extension will load the specified fixture classes.

When **directories** is set, the DoctrineDataFixtures extension will load the data fixtures globed from the respective directories.

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime: 'feature'
      autoload: true
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

This feature is only available for the following SGDB: SQLite, MySQL, PostgreSQL.

It is enabled by default. To disable it, you just have to set `use_backup: false` in the extension configuration:

```yaml
# behat.yml
default:
  # ...
  extensions:
    BehatExtension\DoctrineDataFixturesExtension\Extension:
      lifetime: 'feature'
      autoload: true
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
