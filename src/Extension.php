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

namespace BehatExtension\DoctrineDataFixturesExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Doctrine data fixtures extension for Behat class.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class Extension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'doctrine_data_fixtures';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('autoload')
                    ->defaultTrue()
                ->end()
                ->arrayNode('directories')
                    ->defaultValue([])
                    ->treatFalseLike([])
                    ->treatNullLike([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('fixtures')
                    ->defaultValue([])
                    ->treatFalseLike([])
                    ->treatNullLike([])
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('lifetime')
                    ->defaultValue('feature')
                    ->validate()
                        ->ifNotInArray(['feature', 'scenario'])
                        ->thenInvalid('Invalid fixtures lifetime "%s"')
                    ->end()
                ->end()
                ->booleanNode('use_backup')
                    ->defaultTrue()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.php');

        $keys = ['autoload', 'directories', 'fixtures', 'lifetime', 'use_backup'];

        foreach ($keys as $key) {
            $container->setParameter('behat.doctrine_data_fixtures.'.$key, $config[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
