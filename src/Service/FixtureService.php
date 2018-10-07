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

namespace BehatExtension\DoctrineDataFixturesExtension\Service;

use BehatExtension\DoctrineDataFixturesExtension\EventListener\PlatformListener;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Data Fixture Service.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class FixtureService
{
    private $loader;

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
     * @var null|BackupService
     */
    private $backupService;

    /**
     * @var null|ProxyReferenceRepository
     */
    private $referenceRepository;

    /**
     * FixtureService constructor.
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->loader = new Loader($kernel->getContainer());
    }

    public function enableBackupSupport(BackupService $backupService): void
    {
        $this->backupService = $backupService;
        $this->backupService->setCacheDir($this->kernel->getContainer()->getParameter('kernel.cache_dir'));
    }

    /**
     * Returns the reference repository while loading the fixtures.
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
    private function init(): void
    {
        if (!$this->kernel->getContainer()->has('doctrine')) {
            throw new \RuntimeException('Unable to get Doctrine');
        }
        $doctrine = $this->kernel->getContainer()->get('doctrine');
        if (!$doctrine instanceof ManagerRegistry) {
            throw new \RuntimeException('Unable to get Doctrine');
        }
        $this->listener = new PlatformListener();
        $this->entityManager = $doctrine->getManager();
        $this->entityManager->getEventManager()->addEventSubscriber($this->listener);
    }

    /**
     * Calculate hash on data fixture class names, class file names and modification timestamps.
     */
    private function getHash(): string
    {
        $classNames = array_map('get_class', $this->fixtures);

        foreach ($classNames as &$className) {
            $class = new \ReflectionClass($className);
            $fileName = $class->getFileName();

            $className .= ':'.$fileName.'@'.filemtime($fileName);
        }

        sort($classNames);

        return sha1(serialize([$classNames]));
    }

    /**
     * Fetch fixtures from Doctrine Fixtures Loader.
     */
    private function fetchFixturesFromDoctrineLoader(): void
    {
        if (!$this->kernel->getContainer()->has('doctrine.fixtures.loader.alias')) {
            return;
        }
        $doctrineFixtureLoader = $this->kernel->getContainer()->get('doctrine.fixtures.loader.alias');
        foreach ($doctrineFixtureLoader->getFixtures() as $fixture) {
            if (!$this->loader->hasFixture($fixture)) {
                $this->loader->addFixture($fixture);
            }
        }
    }

    /**
     * Fetch fixtures.
     */
    private function fetchFixtures(): array
    {
        $this->fetchFixturesFromDoctrineLoader();

        return $this->loader->getFixtures();
    }

    private function dispatchEvent(EntityManager $em, string $event): void
    {
        $eventArgs = new LifecycleEventArgs(null, $em);

        $em->getEventManager()->dispatchEvent($event, $eventArgs);
    }

    /**
     * Load fixtures into database.
     */
    private function loadFixtures(): void
    {
        $em = $this->entityManager;

        $this->dispatchEvent($em, 'preTruncate');

        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($em, $purger);
        $executor->setReferenceRepository($this->getReferenceRepository());

        if (null === $this->backupService) {
            $executor->purge();
        }

        $executor->execute($this->fixtures, true);

        $this->dispatchEvent($em, 'postTruncate');
    }

    /**
     * Create database using doctrine schema tool.
     */
    private function createDatabase(): void
    {
        $em = $this->entityManager;
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Drop database using doctrine schema tool.
     */
    private function dropDatabase(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
    }

    /**
     * Cache data fixtures.
     */
    public function cacheFixtures(): void
    {
        $this->init();

        $this->fixtures = $this->fetchFixtures();
        if (!$this->hasBackup()) {
            $this->dropDatabase();
        }
    }

    /**
     * Get backup file path.
     */
    private function getBackupFile(): string
    {
        return $this->backupService->getBackupFile($this->getHash());
    }

    /**
     * Check if there is a backup.
     */
    private function hasBackup(): bool
    {
        if (null === $this->backupService) {
            return false;
        }

        return $this->backupService->hasBackup($this->getHash());
    }

    /**
     * Create a backup for the current fixtures.
     */
    private function createBackup(): void
    {
        if (null === $this->backupService) {
            return;
        }
        $hash = $this->getHash();
        $connection = $this->entityManager->getConnection();

        $this->backupService->createBackup($connection, $hash);
    }

    /**
     * Restore a backup for the current fixtures.
     */
    private function restoreBackup(): void
    {
        if (null === $this->backupService) {
            return;
        }
        $hash = $this->getHash();
        $connection = $this->entityManager->getConnection();

        $this->backupService->restoreBackup($connection, $hash);
    }

    /**
     * Reload data fixtures.
     */
    public function reloadFixtures(): void
    {
        if (null === $this->backupService) {
            $this->dropDatabase();
            $this->createDatabase();
            $this->loadFixtures();

            return;
        }

        if ($this->hasBackup()) {
            $this->restoreBackup();
            $this->getReferenceRepository()->load($this->getBackupFile());

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
    public function flush(): void
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
