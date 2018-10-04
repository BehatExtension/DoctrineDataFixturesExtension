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

namespace BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;

class ProductManager
{
    private $class;

    private $managerRegistry;

    private $entityManager;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->class = Product::class;
        $this->managerRegistry = $managerRegistry;
        $this->entityManager = $this->managerRegistry->getManagerForClass($this->class);
    }

    public function create(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * @return Product[]
     */
    public function all(): array
    {
        /** @var ProductRepository $entityRepository */
        $entityRepository = $this->entityManager->getRepository($this->class);

        return $entityRepository->findAll();
    }
}
