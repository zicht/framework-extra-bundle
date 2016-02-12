<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zicht\Util\Str as StrUtil;
use Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry;
use Zicht\Itertools as iter;
use Twig_Extension;

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


    function __construct(EmbedHelper $embedHelper, AnnotationRegistry $registry, TranslatorInterface $translator = null, SecurityContextInterface $context = null)
    {
        $this->embedHelper        = $embedHelper;
        $this->annotationRegistry = $registry;
        $this->translator         = $translator;
        $this->context            = $context;
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
            new \Twig_SimpleFilter('truncate_html',  array($this, 'truncateHtml')),
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

            new \Twig_SimpleFilter('where',          array($this, 'where')),
            new \Twig_SimpleFilter('not_where',      array($this, 'notWhere')),
            new \Twig_SimpleFilter('where_split',    array($this, 'whereSplit')),
            new \Twig_SimpleFilter('url_to_form_params',    array($this, 'urlToFormParameters')),
            new \Twig_SimpleFilter('url_strip_query',       array($this, 'urlStripQuery')),

            new \Twig_SimpleFilter('ceil',          'ceil'),
            new \Twig_SimpleFilter('floor',         'floor'),
            new \Twig_SimpleFilter('groups',        array($this, 'groups')),
            new \Twig_SimpleFilter('sort_by_type',  array($this, 'sortByType')),
            new \Twig_SimpleFilter('html2text',     array($this, 'html2text')),
            new \Twig_SimpleFilter('replace_recursive', 'array_replace_recursive'),
            new \Twig_SimpleFilter('json_decode',     array($this, 'json_decode')),
            new \Twig_SimpleFilter('sha1', array($this, 'sha1')),
            new \Twig_SimpleFilter('unique', array($this, 'unique')),

            new \Twig_SimpleFilter('sum', array($this, 'sum')),
            new \Twig_SimpleFilter('groupby', array($this, 'groupby')),
            new \Twig_SimpleFilter('sorted', array($this, 'sorted')),
            new \Twig_SimpleFilter('map', array($this, 'map')),
            new \Twig_SimpleFilter('zip', '\Zicht\Itertools\zip'),
            new \Twig_SimpleFilter('chain', '\Zicht\Itertools\chain'),

            new \Twig_SimpleFilter('form_root', array($this, 'form_root')),
            new \Twig_SimpleFilter('form_has_errors', array($this, 'form_has_errors')),
        );
    }

    /**
     * Returns the root of the form
     *
     * @param FormView $formView
     * @return FormView
     */
    public function form_root(FormView $formView)
    {
        return EmbedHelper::getFormRoot($formView);
    }


    /**
     * Returns true when the form, or any of its children, has one or more errors.
     *
     * @param $form
     */
    public function form_has_errors(FormView $form)
    {
        if ($form->vars['errors']->count()) {
            return true;
        }

        foreach ($form->children as $child) {
            if ($this->form_has_errors($child)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Call the php json_decode
     *
     * @param $string
     * @param bool $assoc
     * @return mixed|null
     */
    public function json_decode($string, $assoc = false)
    {
        if (is_string($string)) {
            return json_decode($string, $assoc);
        }
        return null;
    }

    /**
     * Returns a 40 byte string representing the sha1 digest of the input string.
     *
     * @param $string
     * @return string
     */
    public function sha1($string)
    {
        if (is_string($string)) {
            return sha1($string);
        }
        return '';
    }

    /**
     * Takes an array or iterator and returns an array where all items occur only once.
     *
     * @param array|\Traversable
     * @return array
     */
    public function unique($items)
    {
        return array_unique($items instanceof \Traversable ? iterator_to_array($items) : $items, SORT_REGULAR);
    }

    /**
     * Filter a collection based on properties of the collection's items
     *
     * @param array|Collection $items
     * @param array $keyValuePairs
     * @param string $comparator
     * @param string $booleanOperator
     * @return \Doctrine\Common\Collections\Collection
     */
    public function where($items, array $keyValuePairs, $comparator = 'eq', $booleanOperator = 'and')
    {
        if (is_array($items)) {
            $items = new ArrayCollection($items);
        }
        if ($items instanceof PersistentCollection && !$items->isInitialized()) {
            $items->initialize();
        }

        $whereMethod = $booleanOperator . 'Where';

        $eb = new ExpressionBuilder();
        $criteria = new Criteria();
        foreach ($keyValuePairs as $key => $value) {
            if (is_array($value)) {
                if ($comparator === 'eq') {
                    $criteria->$whereMethod($eb->in($key, $value));
                    continue;
                } elseif ($comparator === 'neq') {
                    $criteria->$whereMethod($eb->notIn($key, $value));
                    continue;
                }
            }
            $criteria->$whereMethod($eb->$comparator($key, $value));
        }

        return $items->matching($criteria);
    }

    /**
     * Inverse of where, i.e. get all items that are NOT matching the criteria.
     *
     * @param array|Collection $items
     * @param array $keyValuePairs
     * @return Collection
     */
    public function notWhere($items, $keyValuePairs)
    {
        return $this->where($items, $keyValuePairs, 'neq');
    }

    /**
     * Splits a list in two collections, one matching the criteria, and the rest
     *
     * @param array|Collection $items
     * @param array $keyValuePairs
     * @return Collection
     */
    public function whereSplit($items, $keyValuePairs)
    {
        return array(
            $this->notWhere($items, $keyValuePairs),
            $this->where($items, $keyValuePairs)
        );
    }


    /**
     * Removes the query string from a form.
     *
     * Used as follows in conjunction with url_parse_query()
     *
     * <form method="get" action="{{ url|url_strip_query }}">
     *     {% for k, value in url|url_to_form_params %}
     *          <input type="hidden" name="{{ k }}" value="{{ value }}">
     *     {% endfor %}
     * </form>
     *
     * @param string $url
     * @return array
     */
    public function urlStripQuery($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        return str_replace('?' . $query, '', $url);
    }


    /**
     *
     * @param $url
     * @return array
     */
    public function urlToFormParameters($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $vars = array();
        parse_str($query, $vars);
        return $this->valuesToFormParameters($vars, null);
    }

    /**
     * Prepares a nested array for use in form fields.
     *
     * @param mixed[] $values
     * @param string $parent
     * @return array
     */
    private function valuesToFormParameters($values, $parent)
    {
        $ret = array();
        foreach ($values as $key => $value) {
            if (null !== $parent) {
                $keyName = sprintf('%s[%s]', $parent, $key);
            } else {
                $keyName = $key;
            }
            if (is_scalar($value)) {
                $ret[$keyName]= $value;
            } else {
                $ret = array_merge($ret, $this->valuesToFormParameters($value, $keyName));
            }
        }
        return $ret;
    }


    function getFunctions()
    {
        return array(
            'embed_params' => new \Twig_Function_Method($this, 'getEmbedParams'),
            'first'    => new \Twig_Function_Method($this, 'first'),
            'last'     => new \Twig_Function_Method($this, 'last'),
            'defaults' => new \Twig_Function_Method($this, 'getDefaultOf'),
            'embed'    => new \Twig_Function_Method($this, 'embed'),
            'is_granted'    => new \Twig_Function_Method($this, 'isGranted'),
        );
    }


    /**
     * The template may assume that the role will be denied when there is no security context, therefore we override
     * the default behaviour here.
     *
     * @param string $role
     * @param mixed $object
     * @param mixed $field
     * @return bool
     */
    public function isGranted($role, $object = null, $field = null)
    {
        if (null === $this->context || !$this->context->getToken()) {
            return false;
        }

        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }

        return $this->context->isGranted($role, $object);
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
    public function truncate($str, $length, $ellipsis = '...')
    {
        $result = '';
        foreach (preg_split('/\b/U', $str) as $part) {
            if (strlen($result . $part) > $length) {
                $result = rtrim($result) . $ellipsis;
                break;
            } else {
                $result .= $part;
            }
        }

        return $result;
    }

    /**
     * Truncates html as text
     *
     * @param string $html
     * @param int $length
     * @param string $ellipsis
     * @return string
     */
    public function truncateHtml($html, $length, $ellipsis = '...')
    {
        return $this->truncate(html_entity_decode(strip_tags($html), null, 'UTF-8'), $length, $ellipsis);
    }

    /**
     * @return array|\Twig_NodeVisitorInterface[]
     */
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


    function getEmbedParams()
    {
        return array_filter($this->embedHelper->getEmbedParams());
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


    public function html2text($html)
    {
        return strip_tags($html);
    }

    public function sum($iterable, $default = 0)
    {
        $result = $default;
        foreach (iter\accumulate($iterable) as $result) {};
        return $result;
    }

    public function groupby($iterable, $keyStrategy)
    {
        return iter\groupby($keyStrategy, $iterable);
    }

    public function sorted($iterable, $keyStrategy, $reverse = false)
    {
        return iter\sorted($keyStrategy, $iterable, $reverse);
    }

    public function map($iterable, $keyStrategy)
    {
        return iter\map($keyStrategy, $iterable);
    }
}
