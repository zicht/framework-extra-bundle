<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use \Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class ZichtFrameworkExtraExtension extends DIExtension
{
    /**
     * @{inheritDoc}
     */
    public function load(array $config, ContainerBuilder $container) {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($container->hasDefinition('doctrine')) {
//            $container->getDefinition('doctrine')->addMethodCall()
        }
    }
}