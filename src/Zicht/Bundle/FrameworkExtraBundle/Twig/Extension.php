<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig_Extension;
use Twig_Filter_Function;

class Extension extends Twig_Extension {
    function getFilters() {
        return array(
            'dump' => new \Twig_Filter_Method($this, 'dump'),
            'truncate' => new \Twig_Filter_Method($this, 'truncate'),
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

    function getName() {
        return 'ZichtTwigExtension';
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
