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

class Extension extends Twig_Extension
{
    public static $RELATIVE_DATE_PART_MAP = array(
        'y' => array('year', 'years'),
        'm' => array('month', 'months'),
        'd' => array('day', 'days'),
        'w' => array('week', 'weeks'),
        'h' => array('hour', 'hours'),
        'i' => array('minute', 'minutes'),
        's' => array('second', 'seconds')
    );


    function __construct(EmbedHelper $embedHelper, AnnotationRegistry $registry, \Symfony\Component\Translation\TranslatorInterface $translator = null)
    {
        $this->embedHelper = $embedHelper;
        $this->annotationRegistry = $registry;
        $this->translator = $translator;
    }


    function getFilters() {
        return array(
            'dump'          => new \Twig_Filter_Method($this, 'dump', array('is_safe' => array('html'))),
            'truncate'      => new \Twig_Filter_Method($this, 'truncate'),
            'regex_replace' => new \Twig_Filter_Method($this, 'regex_replace'),
            'str_uscore'    => new \Twig_Filter_Method($this, 'str_uscore'),
            'str_dash'      => new \Twig_Filter_Method($this, 'str_dash'),
            'str_camel'     => new \Twig_Filter_Method($this, 'str_camel'),
            'date_format'   => new \Twig_Filter_Method($this, 'date_format'),
            'relative_date' => new \Twig_Filter_Method($this, 'relative_date'),
            'ga_trackevent' => new \TWig_Filter_Method($this, 'ga_trackevent')
        );
    }


    public function ga_trackevent($values = null)
    {
        $values = func_get_args();
        array_unshift($values, '_trackEvent');

        return sprintf(
            ' onclick="_gaq.push(%s);"',
            htmlspecialchars(json_encode(array_values($values)))
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



    public function relative_date($date)
    {
        $now = new \DateTime();
        $diff = $date->diff($now);
        // natively, diff doesn't contain 'weeks'.
        $diff->w = round($diff->d / 7);
        $message = '';
        foreach (array('y', 'm', 'w', 'd', 'h', 'i', 's') as $part) {
            if ($diff->$part > 0) {
                list($singular, $plural) = self::$RELATIVE_DATE_PART_MAP[$part];
                if ($diff->$part > 1) {
                    $denominator = $plural;
                } else {
                    $denominator = $singular;
                }

                if (null !== $this->translator) {
                    $message = $this->translator->trans(
                        '%count% ' . $denominator . ' ago',
                        array('%count%' => $diff->$part)
                    );
                } else {
                    $message = sprintf('%d %s ago', $diff->$part, $denominator);
                }
                break;
            }
        }
        return $message;
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
