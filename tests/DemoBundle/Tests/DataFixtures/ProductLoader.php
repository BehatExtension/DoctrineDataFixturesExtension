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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductLoader extends Fixture
{
    public function load(ObjectManager $manager)
    {
        array_map(function (array $item) use ($manager) {
            $product = new Product(
                $item['name'],
                $item['description']
            );
            $manager->persist($product);
            $manager->flush();
        }, $this->getData());
    }

    private function getData(): array
    {
        return [
            [
                'name'        => 'Product #1',
                'description' => 'This is the product number 1',
            ],
            [
                'name'        => 'Product #2',
                'description' => 'This is the product number 2',
            ],
        ];
    }
}
