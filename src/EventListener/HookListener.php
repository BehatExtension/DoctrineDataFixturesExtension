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

namespace BehatExtension\DoctrineDataFixturesExtension\EventListener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hook listener.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class HookListener implements EventSubscriberInterface
{
    /**
     * @var string feature|scenario
     */
    private $lifetime;

    /**
     * @var FixtureService
     */
    private $fixtureService;

    /**
     * Constructor.
     *
     * @param string $lifetime
     */
    public function __construct(string $lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ExerciseCompleted::BEFORE => 'beforeExercise',
            FeatureTested::BEFORE     => 'beforeFeature',
            FeatureTested::AFTER      => 'afterFeature',
            ExampleTested::BEFORE     => 'beforeScenario',
            ScenarioTested::BEFORE    => 'beforeScenario',
            ExampleTested::AFTER      => 'afterScenario',
            ScenarioTested::AFTER     => 'afterScenario',
        ];
    }

    /**
     * Set fixture service.
     *
     * @param FixtureService $service
     */
    public function setFixtureService(FixtureService $service)
    {
        $this->fixtureService = $service;
    }

    /**
     * Listens to "exercise.before" event.
     *
     * @param ExerciseCompleted $event
     */
    public function beforeExercise(ExerciseCompleted $event)
    {
        $this->fixtureService->cacheFixtures();
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param FeatureTested $event
     */
    public function beforeFeature(FeatureTested $event)
    {
        if ('feature' !== $this->lifetime) {
            return;
        }

        $this->fixtureService->reloadFixtures();
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param FeatureTested $event
     */
    public function afterFeature(FeatureTested $event)
    {
        if ('feature' !== $this->lifetime) {
            return;
        }

        $this->fixtureService->flush();
    }

    /**
     * Listens to "scenario.before" and "outline.example.before" event.
     *
     * @param ScenarioTested $event
     */
    public function beforeScenario(ScenarioTested $event)
    {
        if ('scenario' !== $this->lifetime) {
            return;
        }

        $this->fixtureService->reloadFixtures();
    }

    /**
     * Listens to "scenario.after" and "outline.example.after" event.
     *
     * @param ScenarioTested $event
     */
    public function afterScenario(ScenarioTested $event)
    {
        if ('scenario' !== $this->lifetime) {
            return;
        }

        $this->fixtureService->flush();
    }
}
