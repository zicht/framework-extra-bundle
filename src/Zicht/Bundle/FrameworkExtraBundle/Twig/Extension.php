<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use \Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry;
use Twig_Extension;
use Twig_Filter_Function;

class Extension extends Twig_Extension {
    function __construct(EmbedHelper $embedHelper, AnnotationRegistry $registry)
    {
        $this->embedHelper = $embedHelper;
        $this->annotationRegistry = $registry;
    }


    function getFilters() {
        return array(
            'dump'          => new \Twig_Filter_Method($this, 'dump'),
            'truncate'      => new \Twig_Filter_Method($this, 'truncate'),
            'regex_replace' => new \Twig_Filter_Method($this, 'regex_replace'),
            'str_uscore'    => new \Twig_Filter_Method($this, 'str_uscore'),
            'str_dash'      => new \Twig_Filter_Method($this, 'str_dash'),
            'str_camel'     => new \Twig_Filter_Method($this, 'str_camel'),
            'date_format'   => new \Twig_Filter_Method($this, 'date_format')
        );
    }



    public function str_dash($str)
    {
        return \Zicht\Util\Str::dash($str);
    }

    public function str_uscore($str)
    {
        return \Zicht\Util\Str::uscore($str);
    }

    public function str_camel($str)
    {
        return \Zicht\Util\Str::camel($str);
    }

    public function date_format($date, $format = '%e %b %Y')
    {
        if ($date instanceof \DateTime) {
            $ts = $date->getTimestamp();
        } elseif (is_numeric($date)) {
            $ts = $date;
        } else {
            throw new \InvalidArgumentException(sprintf("Cannot format %s as a date", $date));
        }

        return strftime($format, $ts);
    }


    /**
     * Adds
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'defaults' => new \Twig_Function_Method($this, 'getDefaultOf'),
            'embed' => new \Twig_Function_Method($this, 'embed'),
        );
    }


    public function regex_replace($subject, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $subject);
    }

    /**
     * Truncate text at a maximum length, splitting by words, and add an ellipsis "...".
     *
     * @param string $str
     * @param int $length
     * @param string $ellipsis
     * @return string
     */
    function truncate($str, $length, $ellipsis = '...') {
        $result = '';
        foreach (preg_split('/\b/', $str) as $part) {
            if (strlen($result . $part) > $length) {
                $result .= $ellipsis;
                break;
            } else {
                $result .= $part;
            }
        }
        return $result;
    }

    public function getNodeVisitors()
    {
        return array(
            'zicht_render_add_embed_params' => new RenderAddEmbedParamsNodeVisitor()
        );
    }


    public function getTokenParsers() {
        return array(
            'zicht_meta_annotate' => new \Zicht\Bundle\FrameworkExtraBundle\Twig\Meta\AnnotateTokenParser(),
            'zicht_meta_annotations' => new \Zicht\Bundle\FrameworkExtraBundle\Twig\Meta\AnnotationsTokenParser()
        );
    }


    public function getAnnotationRegistry()
    {
        return $this->annotationRegistry;
    }


    function embed($urlOrArray)
    {
        $embedParams = array_filter($this->embedHelper->getEmbedParams());
        if (!$embedParams) {
            return $urlOrArray;
        }
        if (is_array($urlOrArray)) {
            return $urlOrArray + $embedParams;
        } elseif (is_string($urlOrArray)) {
            $query = parse_url($urlOrArray, PHP_URL_QUERY);
            $urlOrArray = str_replace($query, '', $urlOrArray);
            $currentParams = array();
            parse_str($query, $currentParams);
            $currentParams += $embedParams;
            return preg_replace('/\?$/', '', $urlOrArray) . '?' . http_build_query($currentParams);
        } else {
            throw new \InvalidArgumentException("Only supports arrays or strings");
        }
    }

    function getName() {
        return 'zicht_framework_extra';
    }


    public function getDefaultOf() {
        $items = func_get_args();
        $items = array_filter($items);
        if (count($items)) {
            return current($items);
        }
        return null;
    }

    /**
     * @param $var
     * @param string $parameters
     * @return mixed
     */
    function dump($var, $parameters = 'doc') {
        switch ($parameters) {
            case 'export':
                \Doctrine\Common\Util\Debug::dump($var,5);
                break;
            default:
            case 'var_dump':
                var_dump($var);
                break;
        }
    }
}
