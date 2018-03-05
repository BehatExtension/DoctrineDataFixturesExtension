<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use BehatExtension\DoctrineDataFixturesExtension\Service\Backup\BackupInterface;
use BehatExtension\DoctrineDataFixturesExtension\Service\Backup\MysqlDumpBackup;
use BehatExtension\DoctrineDataFixturesExtension\Service\Backup\PostgresqlDumpBackup;
use BehatExtension\DoctrineDataFixturesExtension\Service\Backup\SqliteCopyBackup;
use BehatExtension\DoctrineDataFixturesExtension\Service\BackupService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Process\Process;

return function (ContainerConfigurator $container) {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire();

    $container
        ->instanceof(BackupInterface::class)
        ->tag('behat.fixture_extension.backup_service');

    if (class_exists(Process::class)) {
        $container->set(MysqlDumpBackup::class);
        $container->set(PostgresqlDumpBackup::class);
    }
    $container->set(SqliteCopyBackup::class);
    $container->set(BackupService::class);
};
