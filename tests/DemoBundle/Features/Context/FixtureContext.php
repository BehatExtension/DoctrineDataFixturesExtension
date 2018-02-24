<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Tests\DemoBundle\Features\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;

class FixtureContext implements KernelAwareContext
{
    private $kernel;

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I list lines in the entity table
     */
    public function iListLinesInTheEntityTable()
    {
        throw new PendingException();
    }

    /**
     * @Then I should see records
     */
    public function iShouldSeeRecords()
    {
        throw new PendingException();
    }
}
