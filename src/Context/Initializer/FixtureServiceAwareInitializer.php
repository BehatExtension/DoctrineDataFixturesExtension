<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use BehatExtension\DoctrineDataFixturesExtension\Context\FixtureServiceAwareContextInterface;
use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;

/**
 * Class FixtureServiceAwareInitializer.
 */
class FixtureServiceAwareInitializer implements ContextInitializer
{
    /**
     * @var FixtureService
     */
    private $fixtureService;

    /**
     * Constructor.
     */
    public function __construct(FixtureService $fixtureService)
    {
        $this->fixtureService = $fixtureService;
    }

    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof FixtureServiceAwareContextInterface && !$this->usesReferenceDictionary($context)) {
            return;
        }

        $context->setFixtureService($this->fixtureService);
    }

    /**
     * Checks whether the context uses the ReferenceDictionary trait.
     *
     * @param Context $context
     *
     * @return bool
     */
    private function usesReferenceDictionary(Context $context)
    {
        $refl = new \ReflectionObject($context);

        if (!method_exists($refl, 'getTraitNames')) {
            return false;
        }

        if (!in_array('BehatExtension\DoctrineDataFixturesExtension\Context\ReferenceDictionary', $refl->getTraitNames())) {
            return false;
        }

        return true;
    }
}
