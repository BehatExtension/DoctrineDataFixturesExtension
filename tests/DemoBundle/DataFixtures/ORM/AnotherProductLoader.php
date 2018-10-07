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

namespace BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\DataFixtures\ORM;

use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity\Product;
use BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity\ProductManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AnotherProductLoader extends Fixture
{
    private $service;

    public function __construct(ProductManager $service)
    {
        $this->service = $service;
    }

    public function load(ObjectManager $manager)
    {
        array_map(function (array $item) {
            $product = new Product(
                $item['name'],
                $item['description']
            );
            $this->service->create($product);
        }, $this->getData());
    }

    private function getData(): array
    {
        return [
            [
                'name'        => 'Product #9',
                'description' => 'This is the product number 9',
            ],
            [
                'name'        => 'Product #10',
                'description' => 'This is the product number 10',
            ],
        ];
    }
}
