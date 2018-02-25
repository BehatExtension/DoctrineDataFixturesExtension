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

use BehatExtension\DoctrineDataFixturesExtension\Context\Initializer\FixtureServiceAwareInitializer;
use BehatExtension\DoctrineDataFixturesExtension\EventListener\HookListener;
use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container) {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire();

    $container->set(HookListener::class)
        ->args([
            '%behat.doctrine_data_fixtures.lifetime%',
        ])
        ->call('setFixtureService', [
            ref(FixtureService::class),
        ])
        ->tag('event_dispatcher.subscriber');
    $container->set(FixtureService::class)
        ->args([
            ref('symfony2_extension.kernel'),
            '%behat.doctrine_data_fixtures.autoload%',
            '%behat.doctrine_data_fixtures.fixtures%',
            '%behat.doctrine_data_fixtures.directories%',
            '%behat.doctrine_data_fixtures.use_backup%',
        ]);
    $container->set(FixtureServiceAwareInitializer::class)
        ->args([
            ref(FixtureService::class),
        ])
        ->tag('context.initializer');
};
