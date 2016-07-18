<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Class Pass
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler
 */
class Pass implements CompilerPassInterface
{
    /**
     * Adds extra configurations
     *
     * @param ContainerBuilder $container
     * @return null
     */
    public function process(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
        $loader->load('services.xml');

        if ($container->hasDefinition('doctrine')) {
            $loader->load('doctrine.xml');
        }

        if ($container->hasDefinition('liip_imagine.filter.manager')) {
            $loader->load('imagine.xml');
        }
    }
}
