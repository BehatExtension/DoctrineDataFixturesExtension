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
use BehatExtension\DoctrineDataFixturesExtension\Service\BackupService;
use BehatExtension\DoctrineDataFixturesExtension\Service\FixtureService;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Doctrine data fixtures extension for Behat class.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
final class Extension implements ExtensionInterface
{
    public function getConfigKey()
    {
        return 'doctrine_data_fixtures';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('autoload')
                    ->info('When true, the extension will load the data fixtures for all registered bundles')
                    ->defaultFalse()
                    ->setDeprecated('This option has no effect anymore. For each bundle, please declare your fixtures as services and tagged with "doctrine.fixture.orm".')
                ->end()
                ->booleanNode('use_backup')
                    ->info('When true, the extension will backup the database and restore it when needed')
                    ->defaultTrue()
                ->end()
                ->arrayNode('directories')
                    ->defaultValue([])
                    ->treatFalseLike([])
                    ->treatNullLike([])
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('fixtures')
                    ->defaultValue([])
                    ->treatFalseLike([])
                    ->treatNullLike([])
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
                ->scalarNode('lifetime')
                    ->defaultValue('feature')
                    ->validate()
                        ->ifNotInArray(['feature', 'scenario'])
                        ->thenInvalid('Invalid fixtures lifetime "%s"')
                    ->end()
                ->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.php');

        $container->setParameter('behat.doctrine_data_fixtures.use_backup', $config['use_backup']);
        if ($config['use_backup']) {
            $loader->load('backup.php');
        }

        $keys = ['lifetime', 'autoload', 'fixtures', 'directories'];
        foreach ($keys as $key) {
            $container->setParameter('behat.doctrine_data_fixtures.'.$key, $config[$key]);
        }
    }

    public function process(ContainerBuilder $container)
    {
        //Backup Services
        if (!$container->hasDefinition(BackupService::class)) {
            return;
        }
        $backupService = $container->getDefinition(BackupService::class);
        $taggedServices = $container->findTaggedServiceIds('behat.fixture_extension.backup_service');
        foreach ($taggedServices as $id => $attributes) {
            $backupService->addMethodCall('addBackupService', [new Reference($id)]);
        }

        $fixtureService = $container->getDefinition(FixtureService::class);
        $fixtureService->addMethodCall('enableBackupSupport', [new Reference(BackupService::class)]);
    }
}
