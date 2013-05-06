<?php
/**
 * Extended environmnent for the drupal version
 * Part of the Drupal twig extension distribution
 *
 * http://renebakx.nl/twig-for-drupal
 */

class TFD_Environment extends Twig_Environment
{
    /**
     * returns the name of the class to be created
     * which is also the name of the cached instance
     *
     * @param <string> $name of template
     * @return <string>
     */
    public function getTemplateClass($name)
    {
        $cache = preg_replace('/\.tpl.html$/', '', $this->loader->getCacheKey($name));
        return str_replace(array('-', '.', '/'), "_", $cache);
    }


    public function getCacheFilename($name)
    {
        if ($cache = $this->getCache()) {
            $name = preg_replace('/\.tpl.html$/', '', $this->loader->getCacheKey($name));
            $name .= '.php';
            $dir = $cache . '/' . dirname($name);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception("Cache directory $cache is not deep writable?");
                }
            }
            return $cache . '/' . $name;
        }
    }


    public function flushCompilerCache()
    {
        // do a child-first removal of all files and directories in the
        // compiler cache directory
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getCache()), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $file) {
            if(!in_array($file->getFilename(), array('.', '..'))) {
                if ($file->isFile()) {
                    unlink($file);
                } elseif ($file->isDir()) {
                    rmdir($file);
                }
            }
        }
    }
}
