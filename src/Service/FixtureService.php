<?php

declare(strict_types = 1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Service;

use BehatExtension\DoctrineDataFixturesExtension\EventListener\PlatformListener;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Data Fixture Service.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class FixtureService
{
    private $loader;

    /**
     * @var bool
     */
    private $autoload;

    /**
     * @var array
     */
    private $fixtures;

    /**
     * @var array
     */
    private $directories;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PlatformListener
     */
    private $listener;

    /**
     * @var bool
     */
    private $useBackup;

    /**
     * @var BackupService
     */
    private $backupService;

    /**
     * @var null|ProxyReferenceRepository
     */
    private $referenceRepository;

    /**
     * FixtureService constructor.
     *
     * @param Kernel        $kernel
     * @param bool          $autoload
     * @param array         $fixtures
     * @param array         $directories
     * @param bool          $useBackup
     * @param BackupService $backupService
     */
    public function __construct(Kernel $kernel, bool $autoload, array $fixtures, array $directories, bool $useBackup, BackupService $backupService)
    {
        $this->kernel = $kernel;
        $this->autoload = $autoload;
        $this->fixtures = $fixtures;
        $this->directories = $directories;
        $this->useBackup = $useBackup;
        $this->loader = new Loader();

        if ($this->useBackup) {
            $this->backupService = $backupService;
            $this->backupService->setCacheDir($this->kernel->getContainer()->getParameter('kernel.cache_dir'));
        }
    }

    /**
     * Returns the reference repository while loading the fixtures.
     *
     * @return ProxyReferenceRepository
     */
    public function getReferenceRepository(): ProxyReferenceRepository
    {
        if (!$this->referenceRepository) {
            $this->referenceRepository = new ProxyReferenceRepository($this->entityManager);
        }

        return $this->referenceRepository;
    }

    /**
     * Lazy init.
     */
    private function init()
    {
        $this->listener = new PlatformListener();
        $this->entityManager = $this->kernel->getContainer()->get('doctrine')->getManager();
        $this->entityManager->getEventManager()->addEventSubscriber($this->listener);
    }

    private function getHash(): string
    {
        return $this->generateHash($this->fixtures);
    }

    /**
     * Calculate hash on data fixture class names, class file names and modification timestamps.
     *
     * @param array $fixtures
     *
     * @return string
     */
    private function generateHash(array $fixtures): string
    {
        $classNames = array_map('get_class', $fixtures);

        foreach ($classNames as &$className) {
            $class = new \ReflectionClass($className);
            $fileName = $class->getFileName();

            $className .= ':'.$fileName.'@'.filemtime($fileName);
        }

        sort($classNames);

        return sha1(serialize([$classNames]));
    }

    /**
     * Get bundle fixture directories.
     *
     * @return array Array of directories
     */
    private function getBundleFixtureDirectories(): array
    {
        return array_filter(
            array_map(
                function(Bundle $bundle): ?string {
                    $path = $bundle->getPath().'/DataFixtures/ORM';

                    return is_dir($path) ? $path : null;
                },
                $this->kernel->getBundles()
            )
        );
    }

    /**
     * Fetch fixtures from directories.
     *
     * @param array $directoryNames
     */
    private function fetchFixturesFromDirectories(array $directoryNames)
    {
        foreach ($directoryNames as $directoryName) {
            $this->loader->loadFromDirectory($directoryName);
        }
    }

    /**
     * Load a data fixture class.
     *
     * @param string $className Class name
     */
    private function loadFixtureClass(string $className)
    {
        $fixture = new $className();

        if ($this->loader->hasFixture($fixture)) {
            return;
        }

        $this->loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($dependency);
            }
        }
    }

    /**
     * Fetch fixtures from classes.
     *
     * @param array $classNames
     */
    private function fetchFixturesFromClasses(array $classNames)
    {
        foreach ($classNames as $className) {
            if (substr($className, 0, 1) !== '\\') {
                $className = '\\'.$className;
            }

            if (!class_exists($className, false)) {
                $this->loadFixtureClass($className);
            }
        }
    }

    /**
     * Fetch fixtures.
     *
     * @return array
     */
    private function fetchFixtures(): array
    {
        $bundleDirectories = $this->autoload ? $this->getBundleFixtureDirectories() : [];

        $this->fetchFixturesFromDirectories($bundleDirectories);
        $this->fetchFixturesFromDirectories($this->directories);
        $this->fetchFixturesFromClasses($this->fixtures);

        return $this->loader->getFixtures();
    }

    /**
     * Dispatch event.
     *
     * @param EntityManager $em    Entity manager
     * @param string        $event Event name
     */
    private function dispatchEvent(EntityManager $em, string $event)
    {
        $eventArgs = new LifecycleEventArgs(null, $em);

        $em->getEventManager()->dispatchEvent($event, $eventArgs);
    }

    /**
     * Load fixtures into database.
     */
    private function loadFixtures()
    {
        $em = $this->entityManager;

        $this->dispatchEvent($em, 'preTruncate');

        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($em, $purger);
        $executor->setReferenceRepository($this->getReferenceRepository());

        if (!$this->useBackup) {
            $executor->purge();
        }

        $executor->execute($this->fixtures, true);

        $this->dispatchEvent($em, 'postTruncate');
    }

    /**
     * Create database using doctrine schema tool.
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    private function createDatabase()
    {
        $em = $this->entityManager;
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Drop database using doctrine schema tool.
     */
    private function dropDatabase()
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
    }

    /**
     * Cache data fixtures.
     */
    public function cacheFixtures()
    {
        $this->init();

        $this->fixtures = $this->fetchFixtures();

        if ($this->useBackup && !$this->hasBackup()) {
            $this->dropDatabase();
        }
    }

    /**
     * Get backup file path.
     *
     * @return string
     */
    private function getBackupFile(): string
    {
        return $this->backupService->getBackupFile($this->getHash());
    }

    /**
     * Check if there is a backup.
     *
     * @return bool
     */
    private function hasBackup(): bool
    {
        return $this->backupService->hasBackup($this->getHash());
    }

    /**
     * Create a backup for the current fixtures.
     */
    private function createBackup()
    {
        $hash = $this->getHash();
        $connection = $this->entityManager->getConnection();

        $this->backupService->createBackup($connection, $hash);
    }

    /**
     * Restore a backup for the current fixtures.
     */
    private function restoreBackup()
    {
        $hash = $this->getHash();
        $connection = $this->entityManager->getConnection();

        $this->backupService->restoreBackup($connection, $hash);
    }

    /**
     * Reload data fixtures.
     */
    public function reloadFixtures()
    {
        if (!$this->useBackup) {
            $this->loadFixtures();

            return;
        }

        if ($this->hasBackup()) {
            $this->restoreBackup();

            $this->getReferenceRepository()
                    ->load($this->getBackupFile());

            return;
        }

        $this->dropDatabase();
        $this->createDatabase();
        $this->loadFixtures();
        $this->createBackup();

        $this->getReferenceRepository()->save($this->getBackupFile());
    }

    /**
     * Flush entity manager.
     */
    public function flush()
    {
        $em = $this->entityManager;
        $em->flush();
        $em->clear();

        $this->referenceRepository = null;

        $cacheDriver = $em->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
    }
}
