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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container) {
    $container = $container->services()
        ->defaults()
        ->public()
        ->autoconfigure()
        ->autowire();

    $container->set(ProductManager::class)
        ->args([
            ref('doctrine'),
        ]);
    $container->set(ProductLoader::class);
};
