<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Finds the definitions and force them to honour the system's umask() setting.
 */
class FilesystemCacheForceUmaskPass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('annotations.filesystem_cache')) {
            foreach ($container->getDefinitions() as $def) {
                if ($def->getClass() === FilesystemCache::class) {
                    $args = $def->getArguments();
                    if (!isset($args[1])) {
                        $args[1] = (new \ReflectionClass(FilesystemCache::class))->getConstructor()->getParameters()[1]->getDefaultValue();
                    }
                    $args[2] = umask();
                    $def->setArguments($args);
                }
            }
        }
    }
}
