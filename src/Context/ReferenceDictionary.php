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

namespace BehatExtension\DoctrineDataFixturesExtension\Context;

use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;

/**
 * Class ReferenceDictionary.
 */
trait ReferenceDictionary
{
    /**
     * @var FixtureService
     */
    private $fixtureService;

    /**
     * Sets the Reference Repository.
     *
     * @param FixtureService $service
     *
     * @return void
     */
    public function setFixtureService(FixtureService $service): void
    {
        $this->fixtureService = $service;
    }

    /**
     * Returns the Reference Repository.
     *
     * @return FixtureService
     */
    public function getFixtureService(): FixtureService
    {
        return $this->fixtureService;
    }

    /**
     * Takes a reference string and returns the entity created in fixtures.
     *
     * @param string $reference
     *
     * @return object
     */
    public function getReference($reference)
    {
        return $this->fixtureService->getReferenceRepository()->getReference($reference);
    }

    /**
     * Checks if the reference is known to the Repository.
     *
     * @param string $reference
     *
     * @return bool
     */
    public function hasReference($reference): bool
    {
        return $this->fixtureService->getReferenceRepository()->hasReference($reference);
    }
}
