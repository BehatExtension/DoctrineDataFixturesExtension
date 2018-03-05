<?php

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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
                ->scalarNode('autoload')
                    ->defaultValue(true)
                ->end()
                ->variableNode('migrations')
                    ->defaultNull()
                ->end()
                ->arrayNode('directories')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('fixtures')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('lifetime')
                    ->defaultValue('feature')
                    ->validate()
                        ->ifNotInArray(['feature', 'scenario'])
                        ->thenInvalid('Invalid fixtures lifetime "%s"')
                    ->end()
                ->end()
                ->booleanNode('use_backup')
                    ->defaultValue(true)
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.xml');

        if (isset($config['migrations'])) {
            $config['migrations'] = (array) $config['migrations'];

            if (!class_exists('Doctrine\DBAL\Migrations\Migration')) {
                throw new \RuntimeException('Configuration requires doctrine/migrations package');
            }
        }

        $container->setParameter('behat.doctrine_data_fixtures.autoload', $config['autoload']);
        $container->setParameter('behat.doctrine_data_fixtures.directories', $config['directories']);
        $container->setParameter('behat.doctrine_data_fixtures.fixtures', $config['fixtures']);
        $container->setParameter('behat.doctrine_data_fixtures.lifetime', $config['lifetime']);
        $container->setParameter('behat.doctrine_data_fixtures.migrations', $config['migrations']);
        $container->setParameter('behat.doctrine_data_fixtures.use_backup', $config['use_backup']);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
