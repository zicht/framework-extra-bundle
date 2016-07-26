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
        if (!$container->hasExtension('doctrine')) {
            $container->removeDefinition('zicht_framework_extra.form.zicht_parent_choice_type');
        }

        if (!$container->hasExtension('liip_imagine')) {
            $container->removeDefinition('zicht_framework_extra.imagine.match_filter_loader');
        }
    }
}
