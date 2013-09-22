<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use \Symfony\Component\Translation\TranslatorInterface;

use \Zicht\Util\Str as StrUtil;
use \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use \Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry;
use \Twig_Extension;
use \Twig_Filter_Function;

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


    function __construct(EmbedHelper $embedHelper, AnnotationRegistry $registry, TranslatorInterface $translator = null)
    {
        $this->embedHelper        = $embedHelper;
        $this->annotationRegistry = $registry;
        $this->translator         = $translator;
        $this->globals            = array();
    }


    function setGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function getGlobals()
    {
        return $this->globals;
    }

    function getFilters()
    {
        return array(
            'dump'            => new \Twig_Filter_Method($this, 'dump', array('is_safe' => array('html'))),
            'xml'             => new \Twig_Filter_Method($this, 'xml'),
            'truncate'        => new \Twig_Filter_Method($this, 'truncate'),
            'regex_replace'   => new \Twig_Filter_Method($this, 'regex_replace'),
            're_replace'      => new \Twig_Filter_Method($this, 'regex_replace'),
            'str_uscore'      => new \Twig_Filter_Method($this, 'str_uscore'),
            'str_dash'        => new \Twig_Filter_Method($this, 'str_dash'),
            'str_camel'       => new \Twig_Filter_Method($this, 'str_camel'),
            'str_humanize'    => new \Twig_Filter_Method($this, 'str_humanize'),
            'date_format'     => new \Twig_Filter_Method($this, 'date_format'),
            'relative_date'   => new \Twig_Filter_Method($this, 'relative_date'),
            'ga_trackevent'   => new \TWig_Filter_Method($this, 'ga_trackevent'),

            'with'            => new \Twig_Filter_Method($this, 'with'),
            'without'         => new \Twig_Filter_Method($this, 'without'),

            'round'           => new \Twig_Filter_Function('round'),
            'ceil'            => new \Twig_Filter_Function('ceil'),
            'floor'           => new \Twig_Filter_Function('floor'),
            'groups'          => new \Twig_Filter_Method($this, 'groups')
        );
    }


    function getFunctions()
    {
        return array(
            'first'    => new \Twig_Function_Method($this, 'first'),
            'last'     => new \Twig_Function_Method($this, 'last'),
            'defaults' => new \Twig_Function_Method($this, 'getDefaultOf'),
            'embed'    => new \Twig_Function_Method($this, 'embed'),
        );
    }


    function first($list)
    {
        foreach ($list as $item) {
            return $item;
        }

        return null;
    }


    function groups($list, $numGroups)
    {
        $items = ($list instanceof \Traversable ? iterator_to_array($list) : $list);

        $groups = array();
        $i = 0;
        foreach ($items as $item) {
            $groups[$i++ % $numGroups][]= $item;
        }
        return $groups;
    }


    /**
     * Formats some values as an 'onclick' attribute for Google Analytics
     *
     * @param null $values
     * @return string
     */
    public function ga_trackevent($values = null)
    {
        $values = func_get_args();
        array_unshift($values, '_trackEvent');

        return sprintf(
            ' onclick="_gaq.push(%s);"',
            htmlspecialchars(json_encode(array_values($values)))
        );
    }


    public function str_dash($str, $camelFirst = true)
    {
        if ($camelFirst) {
            $str = StrUtil::camel($str);
        }
        return StrUtil::dash($str);
    }

    public function str_uscore($str, $camelFirst = true)
    {
        if ($camelFirst) {
            $str = StrUtil::camel($str);
        }
        return StrUtil::uscore($str);
    }

    public function str_camel($str)
    {
        return StrUtil::camel($str);
    }


    public function str_humanize($str)
    {
        return StrUtil::humanize($str);
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
        $now  = new \DateTime();
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
     * Filter implementation for regular expression replacement
     *
     * @param string $subject
     * @param string $pattern
     * @param string $replacement
     * @return string
     */
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
    function truncate($str, $length, $ellipsis = '...')
    {
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


    public function getTokenParsers()
    {
        return array(
            'zicht_with'             => new ControlStructures\WithTokenParser(),
            'zicht_switch'           => new ControlStructures\SwitchTokenParser(),
            'zicht_meta_annotate'    => new Meta\AnnotateTokenParser(),
            'zicht_meta_annotations' => new Meta\AnnotationsTokenParser()
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
            $query         = parse_url($urlOrArray, PHP_URL_QUERY);
            $urlOrArray    = str_replace($query, '', $urlOrArray);
            $currentParams = array();
            parse_str($query, $currentParams);
            $currentParams += $embedParams;

            return preg_replace('/\?$/', '', $urlOrArray) . '?' . http_build_query($currentParams);
        } else {
            throw new \InvalidArgumentException("Only supports arrays or strings");
        }
    }

    function getName()
    {
        return 'zicht_framework_extra';
    }


    public function getDefaultOf()
    {
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
    public function dump($var, $mode = null)
    {
        if (null === $mode && class_exists('Zicht\Util\Debug')) {
            return htmlspecialchars(\Zicht\Util\Debug::dump($var));
        } else {
            switch ($mode) {
                case 'export':
                    \Doctrine\Common\Util\Debug::dump($var, 5);
                    break;
                default:
                    var_dump($var);
                    break;
            }
        }
        return null;
    }


    public function xml($data)
    {
        if ($data instanceof \SimpleXMLElement) {
            $data = $data->saveXML();
        }
        $dom = new \DOMDocument();
        $dom->loadXml($data);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
