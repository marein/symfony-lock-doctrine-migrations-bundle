<?php

declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('marein_lock_doctrine_migrations');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('lock_name_prefix')->defaultValue('migrate__')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
