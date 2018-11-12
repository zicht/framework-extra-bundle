<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

/**
 * Class RequirejsGlobal
 * Helper twig global for zicht_requirejs configuration
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Twig
 */
class RequirejsGlobal
{
    /**
     * Constructor
     *
     * @param array $config
     * @param bool $debug
     */
    public function __construct($config, $debug = false)
    {
        $this->config = $config;
        $this->debug = $debug;
    }

    /**
     * Set debugging flag. With debugging on, the source files are used as resources in stead of the target files.
     *
     * @param bool $debug
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }

    /**
     * @return null|string
     */
    public function getSourceFile()
    {
        $sourceFile = null;

        if ($this->debug) {
            $sourceFile = sprintf(
                '/%s/%s/%s',
                $this->config['src_dir'],
                $this->config['base_url'],
                $this->config['name']
            );
        } else {
            $sourceFile = sprintf(
                '/%s/%s',
                $this->config['target_dir'],
                $this->config['out']
            );
        }

        return $sourceFile;
    }
}
