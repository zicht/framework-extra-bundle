<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Helper;

/**
 * Provides a global registry for page "annotations", which can be used for meta data such as meta tags, title or
 * OpenGraph data. This can be utilized using the Twig {% annotate %} and {% annotations %} {% endannotations %}
 * constructs.
 */
class AnnotationRegistry
{
    /**
     * The annotations
     *
     * @var array
     */
    private $annotations = array();


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->annotations = array();
    }


    /**
     * Add an annotation.
     *
     * @param string $name
     * @param mixed $value
     * @param int $priority
     * @return void
     */
    public function addAnnotation($name, $value, $priority = 0)
    {
        $this->annotations[]= array('name' => $name, 'value' => $value, 'priority' => $priority);
    }


    /**
     * Get the annotations.
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }


    /**
     * Add multiple annotations, or an annotated object that implements the getPublicAnnotations() method.
     *
     * @param mixed $values
     * @return void
     */
    public function addAnnotations($values)
    {
        // TODO interface
        if (method_exists($values, 'getPublicAnnotations')) {
            $this->addAnnotations($values->getPublicAnnotations());
        } else {
            foreach ($values as $name => $value) {
                $this->addAnnotation($name, $value);
            }
        }
    }
}