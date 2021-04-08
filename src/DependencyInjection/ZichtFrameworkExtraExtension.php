<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;

/**
 * DI extension for the bundle
 */
class ZichtFrameworkExtraExtension extends DIExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (array_key_exists('DoctrineBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('doctrine.xml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['embed_helper'])) {
            $container->getDefinition('zicht_embed_helper')
                ->addMethodCall(
                    'setMarkExceptionsAsFormErrors',
                    [$config['embed_helper']['mark_exceptions_as_errors']]
                );
        }

        if (false === $config['disable_schema-update']) {
            $container->removeDefinition('zicht_framework_extra.event_listener.update_schema_doctrine_command_listener');
        }

        if (class_exists('Zicht\Itertools\twig\Extension')) {
            $definition = new Definition('Zicht\Itertools\twig\Extension');
            // Note that the following two arguments are only available by Itertools v2.15.0 and higher,
            // luckely PHP doesn't care when you provide arguments that are not used, so this is backwards compatible.
            $definition->addArgument($config['itertools']['twig_name']);
            $definition->addArgument($config['itertools']['twig_enable_legacy_api']);
            $definition->addTag('twig.extension');
            $container->setDefinition('zicht_itertools_twig_extension', $definition);
        }
    }
}
