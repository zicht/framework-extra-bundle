<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zicht_framework_extra');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('embed_helper')
                    ->children()
                        ->booleanNode('mark_exceptions_as_errors')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->booleanNode('disable_schema-update')->defaultTrue()->end()
                ->arrayNode('itertools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('twig_name')->defaultValue('it')->end()
                        ->scalarNode('twig_enable_legacy_api')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
