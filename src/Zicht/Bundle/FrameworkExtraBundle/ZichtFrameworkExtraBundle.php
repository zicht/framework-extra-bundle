<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
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
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\Compiler\FilesystemCacheForceUmaskPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\JsonSchemaRefProviderPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\RemoveExtensionsPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ReplaceTranslatorPass());
    }
}
