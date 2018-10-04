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

namespace BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Tests\DataFixtures;

use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity\Product;
use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity\ProductManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductLoaderContainerAware extends Fixture implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var ProductManager $service */
        $service = $this->container->get(ProductManager::class);

        array_map(function (array $item) use ($service) {
            $product = new Product(
                $item['name'],
                $item['description']
            );
            $service->create($product);
        }, $this->getData());
    }

    private function getData(): array
    {
        return [
            [
                'name'        => 'Product #3',
                'description' => 'This is the product number 3',
            ],
            [
                'name'        => 'Product #4',
                'description' => 'This is the product number 4',
            ],
        ];
    }
}
