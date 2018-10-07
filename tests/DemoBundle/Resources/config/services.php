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

use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity\ProductManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Tests\IsolatedProductLoader;

return function (ContainerConfigurator $container) {
    $container = $container->services()
        ->defaults()
        ->public()
        ->autoconfigure()
        ->autowire();

    $container->set(ProductManager::class);
    $container->load(
        'BehatExtension\\DoctrineDataFixturesExtension\\Tests\\DemoBundle\\Tests\\DataFixtures\\',
        __DIR__.'/../../Tests/DataFixtures/*'
    )->private();
    $container->load(
        'BehatExtension\\DoctrineDataFixturesExtension\\Tests\\DemoBundle\\DataFixtures\\ORM\\',
        __DIR__.'/../../DataFixtures/ORM/*'
    )->private();
    $container->set(IsolatedProductLoader::class)->private();
};
