<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Platform listener.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class PlatformListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preTruncate',
            'postTruncate',
        ];
    }

    /**
     * Pre-truncate.
     *
     * @param \Doctrine\Common\Persistence\Event\LifecycleEventArgs $args
     */
    public function preTruncate(LifecycleEventArgs $args)
    {
        $connection = $args->getObjectManager()->getConnection();
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof MySqlPlatform) {
            $connection->exec('SET foreign_key_checks = 0;');
        }
    }

    /**
     * Post-truncate.
     *
     * @param \Doctrine\Common\Persistence\Event\LifecyleEventArgs $args
     */
    public function postTruncate(LifecycleEventArgs $args)
    {
        $connection = $args->getObjectManager()->getConnection();
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof MySqlPlatform) {
            $connection->exec('SET foreign_key_checks = 1;');
        }
    }
}
