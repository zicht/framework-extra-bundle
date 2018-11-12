<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Page bundle configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @{inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zicht_framework_extra');

        $rootNode
            ->children()
                ->scalarNode('requirejs')->end()
                ->scalarNode('uglify')->end()
                ->booleanNode('uglify_debug')->end()
                ->arrayNode('embed_helper')
                    ->children()
                        ->booleanNode('mark_exceptions_as_errors')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->booleanNode('disable_schema-update')->defaultTrue()
            ->end();

        return $treeBuilder;
    }
}
