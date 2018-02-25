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
use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Tests\DataFixtures\ProductLoader;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container = $container->services()
        ->defaults()
        ->public()
        ->autoconfigure()
        ->autowire();

    $container
        ->instanceof(ORMFixtureInterface::class)
        ->tag('doctrine.fixture.orm');

    $container->set(ProductManager::class);
    $container->set(ProductLoader::class);
    $container
        ->alias('doctrine.fixtures.loader.alias', 'doctrine.fixtures.loader')
        ->public();
};
