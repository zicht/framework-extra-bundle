<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReplaceTranslatorPass implements CompilerPassInterface
{
    /**
     * Replace the default translator class with our own (if configured) to support `/zz/` browsing
     * {@inheritDoc}
     *
     * @see \Zicht\Bundle\FrameworkExtraBundle\Translation\Translator
     */
    public function process(ContainerBuilder $container)
    {
        if (class_exists(BaseTranslator::class) && $container->hasDefinition('translator.default')
            && $container->hasParameter('translator.class')) {
            /** @var class-string $class */
            $class = $container->getParameter('translator.class');
            $definition = $container->getDefinition('translator.default');
            $definition->setClass($class);
        }
    }
}
