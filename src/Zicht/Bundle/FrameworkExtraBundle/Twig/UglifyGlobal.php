<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

/**
 * Helper twig global for zicht_uglify configuration
 */
class UglifyGlobal implements \ArrayAccess, \IteratorAggregate
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
     * Get the source file prefixed with the source directory.
     *
     * @param string $file
     * @return string
     */
    public function getSourceFile($file)
    {
        return ltrim($this->config['src_dir'] . '/' . $file, '/');
    }


    /**
     * Get the target file prefixed with the source directory.
     *
     * @param string $file
     * @return string
     */
    public function getTargetFile($file)
    {
        return ltrim($this->config['target_dir'] . '/' . $file, '/');
    }


    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->config['resources'][$offset]);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException(__CLASS__ . ' is read-only');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(__CLASS__ . ' is read-only');
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $ret = array();
        foreach (array_keys($this->config['resources']) as $key) {
            $ret[$key] = $this[$key];
        }
        return new \ArrayIterator($ret);
    }
}
