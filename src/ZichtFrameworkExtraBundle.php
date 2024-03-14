<?php

namespace Zicht\Bundle\FrameworkExtraBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZichtFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\Compiler\RemoveExtensionsPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\FilesystemCacheForceUmaskPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ReplaceTranslatorPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ActivateSearchBotBlockerPass());
    }
}
