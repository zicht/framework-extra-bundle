<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Twig_Extension;
use Twig_Filter_Function;

class Extension extends Twig_Extension {
    function __construct(EmbedHelper $embedHelper)
    {
        $this->embedHelper = $embedHelper;
    }



    function getFilters() {
        return array(
            'dump' => new \Twig_Filter_Method($this, 'dump'),
            'truncate' => new \Twig_Filter_Method($this, 'truncate'),
        );
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
