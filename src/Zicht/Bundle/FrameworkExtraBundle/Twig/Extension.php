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
            new \Twig_SimpleFilter('dump',           array($this, 'dump'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('xml',            array($this, 'xml')),
            new \Twig_SimpleFilter('truncate',       array($this, 'truncate')),
            new \Twig_SimpleFilter('regex_replace',  array($this, 'regex_replace')),
            new \Twig_SimpleFilter('re_replace',     array($this, 'regex_replace')),
            new \Twig_SimpleFilter('str_uscore',     array($this, 'str_uscore')),
            new \Twig_SimpleFilter('str_dash',       array($this, 'str_dash')),
            new \Twig_SimpleFilter('str_camel',      array($this, 'str_camel')),
            new \Twig_SimpleFilter('str_humanize',   array($this, 'str_humanize')),
            new \Twig_SimpleFilter('date_format',    array($this, 'date_format')),
            new \Twig_SimpleFilter('relative_date',  array($this, 'relative_date')),
            new \Twig_SimpleFilter('ga_trackevent',  array($this, 'ga_trackevent')),

            new \Twig_SimpleFilter('with',           array($this, 'with')),
            new \Twig_SimpleFilter('without',        array($this, 'without')),

            new \Twig_SimpleFilter('round',         'round'),
            new \Twig_SimpleFilter('ceil',          'ceil'),
            new \Twig_SimpleFilter('floor',         'floor'),
            new \Twig_SimpleFilter('groups',        array($this, 'groups')),
            new \Twig_SimpleFilter('sort_by_type',  array($this, 'sortByType'))
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

    public function sortByType($collection, $types)
    {
        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        // store a map of the original sorting, so the usort can use this to keep the original sorting if the types are
        // equal
        $idToIndexMap = array();
        foreach ($collection as $index => $item) {
            $idToIndexMap[$item->getId()]= $index;
        }

        $numTypes = count($types);

        usort($collection, function ($left, $right) use($types, $idToIndexMap, $numTypes) {
            $localClassNameLeft = Str::classname(get_class($left));
            $localClassNameRight = Str::classname(get_class($right));

            // if same type, use original sorting
            if ($localClassNameRight === $localClassNameLeft) {
                $indexLeft = $idToIndexMap[$left->getId()];
                $indexRight = $idToIndexMap[$right->getId()];
            } else {
                $indexLeft = array_search($localClassNameLeft, $types);
                $indexRight = array_search($localClassNameRight, $types);

                // assume that types that aren't defined, should come last.
                if (false === $indexLeft) {
                    $indexLeft = $numTypes + 1;
                }
                if (false === $indexRight) {
                    $indexRight = $numTypes + 1;
                }
            }

            if ($indexLeft < $indexRight) {
                return -1;
            }
            if ($indexLeft > $indexRight) {
                return 1;
            }
            return 0;
        });

        return $collection;
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
        } elseif (preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}T[0-9]{2,2}:[0-9]{2,2}.*/', $date)) {
            // timestamp format 2013-01-01T00:00:00
            $ts = new \DateTime($date);
            $ts = $ts->getTimestamp();
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
