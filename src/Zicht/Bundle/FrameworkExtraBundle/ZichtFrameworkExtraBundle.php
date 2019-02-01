<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle entry point
 */
class ZichtFrameworkExtraBundle extends Bundle
{
    /**
     * Build
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\Compiler\RemoveExtensionsPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\FilesystemCacheForceUmaskPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ReplaceTranslatorPass());
    }
}
