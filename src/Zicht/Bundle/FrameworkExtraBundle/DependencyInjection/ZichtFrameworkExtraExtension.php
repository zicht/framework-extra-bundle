<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;

/**
 * DI extension for the bundle
 */
class ZichtFrameworkExtraExtension extends DIExtension
{
    /**
     * Adds the uglify configuration
     *
     * @param string $uglifyConfigFile
     * @param boolean $isDebug
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function addUglifyConfiguration($uglifyConfigFile, $isDebug, ContainerBuilder $container)
    {
        if (!is_file($uglifyConfigFile)) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.uglify setting '$uglifyConfigFile' is not a file"
            );
        }

        $container->addResource(new FileResource($uglifyConfigFile));

        try {
            $uglifyConfig = Yaml::parse(file_get_contents($uglifyConfigFile));
        } catch (\Exception $e) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.uglify setting '$uglifyConfigFile' could not be read",
                0,
                $e
            );
        }

        $global = new Definition(
            'Zicht\Bundle\FrameworkExtraBundle\Twig\UglifyGlobal',
            array(
                $uglifyConfig,
                $isDebug
            )
        );

        $global->addTag('twig.global');
        $global->addMethodCall('setDebug', array($isDebug));
        $container->getDefinition('zicht_twig_extension')->addMethodCall('setGlobal', array('zicht_uglify', $global));
    }

    /**
     * Adds the requirejs configuration
     *
     * @param string $requirejsConfigFile
     * @param boolean $isDebug
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function addRequirejsConfiguration($requirejsConfigFile, $isDebug, ContainerBuilder $container)
    {
        if (!is_file($requirejsConfigFile)) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.requirejs setting '$requirejsConfigFile' is not a file"
            );
        }
        $container->addResource(new FileResource($requirejsConfigFile));
        try {
            $requirejsConfig = Yaml::parse($requirejsConfigFile);
        } catch (\Exception $e) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.requirejs setting '$requirejsConfigFile' could not be read",
                0,
                $e
            );
        }

        $global = new Definition(
            'Zicht\Bundle\FrameworkExtraBundle\Twig\RequirejsGlobal',
            array(
                $requirejsConfig,
                $isDebug
            )
        );

        $global->addTag('twig.global');
        $global->addMethodCall('setDebug', array($isDebug));
        $container->getDefinition('zicht_twig_extension')->addMethodCall('setGlobal', array('zicht_requirejs', $global));
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if ($container->hasExtension('doctrine')) {
            $loader->load('doctrine.xml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['uglify'])) {
            if (!isset($config['uglify_debug'])) {
                $config['uglify_debug']= $container->getParameter('kernel.debug');
            }

            $this->addUglifyConfiguration($config['uglify'], $config['uglify_debug'], $container);
        }

        if (!empty($config['requirejs'])) {
            if (!isset($config['requirejs_debug'])) {
                $config['requirejs_debug']= $container->getParameter('kernel.debug');
            }

            $this->addRequirejsConfiguration($config['requirejs'], $config['requirejs_debug'], $container);
        }

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
            $container->setDefinition('zicht_itertools_twig_extension', (new Definition('Zicht\Itertools\twig\Extension'))->addTag('twig.extension'));
        }
    }
}
