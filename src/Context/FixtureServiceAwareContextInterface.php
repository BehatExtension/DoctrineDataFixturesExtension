<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Context;

use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;

/**
 * Interface FixtureServiceAwareContextInterface.
 */
interface FixtureServiceAwareContextInterface
{
    /**
     * Set the FixtureService.
     *
     * @param FixtureService $service
     *
     * @return mixed
     */
    public function setFixtureService(FixtureService $service);
}
