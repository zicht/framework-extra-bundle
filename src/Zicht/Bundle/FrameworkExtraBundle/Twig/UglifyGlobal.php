<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig;


/**
 * Helper twig global for zicht_uglify configuration
 */
class UglifyGlobal implements \ArrayAccess
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
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }


    /**
     * Get the source file prefixed with the source directory.
     *
     * @param string $file
     * @return string
     */
    public function getSourceFile($file)
    {
        return $this->config['src_dir'] . '/' . $file;
    }


    /**
     * Get the target file prefixed with the source directory.
     *
     * @param string $file
     * @return string
     */
    public function getTargetFile($file)
    {
        return $this->config['target_dir'] . '/' . $file;
    }


    /**
     * @{inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->config['resources'][$offset]);
    }

    /**
     * @{inheritDoc}
     */
    public function offsetGet($offset)
    {
        if ($this->debug) {
            return array_map(
                array($this, 'getSourceFile'),
                $this->config['resources'][$offset]['files']
            );
        } else {
            return array($this->getTargetFile($offset));
        }
    }

    /**
     * @{inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException(__CLASS__ . ' is read-only');
    }

    /**
     * @{inheritDoc}
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(__CLASS__ . ' is read-only');
    }
}