<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class Pass
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler
 */
class RemoveExtensionsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('liip_imagine')) {
            $container->removeDefinition('zicht_framework_extra.imagine.match_filter_loader');
        }
    }
}
