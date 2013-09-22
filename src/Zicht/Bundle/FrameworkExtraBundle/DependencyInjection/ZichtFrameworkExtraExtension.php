<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use Zicht\Bundle\FrameworkExtraBundle\Uglify\TwigUglifyGlobal;
use \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use \Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class ZichtFrameworkExtraExtension extends DIExtension
{
    /**
     * @{inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['uglify'])) {
            $this->addUglifyConfiguration($config['uglify'], $container);
        }
    }


    public function addUglifyConfiguration($uglifyConfigFile, ContainerBuilder $container)
    {
        if (!is_file($uglifyConfigFile)) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.uglify setting '$uglifyConfigFile' is not a file"
            );
        }
        $container->addResource(new \Symfony\Component\Config\Resource\FileResource($uglifyConfigFile));
        try {
            $uglifyConfig = Yaml::parse($uglifyConfigFile);
        } catch (\Exception $e) {
            throw new InvalidConfigurationException(
                "zicht_framework_extra.uglify setting '$uglifyConfigFile' could not be read",
                0,
                $e
            );
        }
        $global = new Definition('Zicht\Bundle\FrameworkExtraBundle\Twig\UglifyGlobal', array(
            $uglifyConfig,
            $container->getParameter('kernel.debug')
        ));
        $global->addTag('twig.global');
        $global->addMethodCall('setDebug', array($container->getParameter('kernel.debug')));
        $container->getDefinition('zicht_twig_extension')->addMethodCall('setGlobal', array('zicht_uglify', $global));
    }
}