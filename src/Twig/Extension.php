<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\PersistentCollection;
use DOMDocument;
use SimpleXMLElement;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry;
use Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Zicht\Util\Debug;
use Zicht\Util\Str as StrUtil;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /** @var array<string, string[]>|array[] */
    public static $RELATIVE_DATE_PART_MAP = [
        'y' => ['year', 'years'],
        'm' => ['month', 'months'],
        'd' => ['day', 'days'],
        'w' => ['week', 'weeks'],
        'h' => ['hour', 'hours'],
        'i' => ['minute', 'minutes'],
        's' => ['second', 'seconds'],
    ];

    /**
     * @var EmbedHelper
     */
    protected $embedHelper;

    /**
     * @var array
     */
    protected $globals;

    /**
     * @var AnnotationRegistry
     */
    protected $annotationRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @param EmbedHelper $embedHelper
     * @param AnnotationRegistry $annotationRegistry
     * @param TranslatorInterface|null $translator
     * @param AuthorizationCheckerInterface|null $authChecker
     */
    public function __construct(
        EmbedHelper $embedHelper,
        AnnotationRegistry $annotationRegistry,
        TranslatorInterface $translator = null,
        AuthorizationCheckerInterface $authChecker = null
    ) {
        $this->globals = [];
        $this->embedHelper = $embedHelper;
        $this->annotationRegistry = $annotationRegistry;
        $this->translator = $translator;
        $this->authChecker = $authChecker;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dump', [$this, 'dump'], ['is_safe' => ['html']]),
            new TwigFilter('xml', [$this, 'xml']),
            new TwigFilter('regex_replace', [$this, 'regexReplace']),
            new TwigFilter('re_replace', [$this, 'regexReplace']),
            new TwigFilter('str_uscore', [$this, 'strUscore']),
            new TwigFilter('str_dash', [$this, 'strDash']),
            new TwigFilter('str_camel', [$this, 'strCamel']),
            new TwigFilter('str_humanize', [$this, 'strHumanize']),
            new TwigFilter('date_format', [$this, 'dateFormat']),
            new TwigFilter('relative_date', [$this, 'relativeDate']),
            new TwigFilter('ga_trackevent', [$this, 'gaTrackEvent']),

            new TwigFilter('prefix_multiple', [$this, 'prefixMultiple']),
            new TwigFilter('trans_multiple', [$this, 'transMultiple']),
            new TwigFilter('truncate_html', [$this, 'truncateHtml']),

            new TwigFilter('with', [$this, 'with']),
            new TwigFilter('without', [$this, 'without']),

            new TwigFilter('where', [$this, 'where']),
            new TwigFilter('not_where', [$this, 'notWhere']),
            new TwigFilter('where_split', [$this, 'whereSplit']),
            new TwigFilter('url_to_form_params', [$this, 'urlToFormParameters']),
            new TwigFilter('url_strip_query', [$this, 'urlStripQuery']),

            new TwigFilter('ceil', 'ceil'),
            new TwigFilter('floor', 'floor'),
            new TwigFilter('groups', [$this, 'groups']),
            new TwigFilter('sort_by_type', [$this, 'sortByType']),
            new TwigFilter('html2text', [$this, 'htmlToText']),
            new TwigFilter('replace_recursive', 'array_replace_recursive'),
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('sha1', [$this, 'shaOne']),

            new TwigFilter('form_root', [$this, 'formRoot']),
            new TwigFilter('form_has_errors', [$this, 'formHasErrors']),
        ];
    }

    /**
     * @param array $values
     * @param string $prefix
     * @return \Generator
     */
    public function prefixMultiple($values, $prefix)
    {
        foreach ($values as $value) {
            yield "${prefix}${value}";
        }
    }

    /**
     * Translate multiple strings
     *
     * @param array $messages
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return \Generator
     */
    public function transMultiple($messages, $parameters = [], $domain = null, $locale = null)
    {
        foreach ($messages as $message) {
            yield  $this->translator->trans($message, $parameters, $domain, $locale);
        }
    }

    /**
     * Returns the root of the form
     *
     * @param FormView $formView
     * @return FormView
     */
    public function formRoot(FormView $formView)
    {
        return EmbedHelper::getFormRoot($formView);
    }

    /**
     * Returns true when the form, or any of its children, has one or more errors.
     *
     * @param FormView $form
     *
     * @return bool
     */
    public function formHasErrors(FormView $form)
    {
        if ($form->vars['errors']->count()) {
            return true;
        }

        foreach ($form->children as $child) {
            if ($this->formHasErrors($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Call the php json_decode
     *
     * @param string $string
     * @param bool $assoc
     * @return mixed|null
     */
    public function jsonDecode($string, $assoc = false)
    {
        if (is_string($string)) {
            return json_decode($string, $assoc);
        }
        return null;
    }

    /**
     * Returns a 40 byte string representing the sha1 digest of the input string.
     *
     * @param string|Markup $string
     * @return string
     */
    public function shaOne($string)
    {
        if (is_string($string) || $string instanceof Markup) {
            return sha1($string);
        }
        return '';
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
     * @return array
     */
    public function whereSplit($items, $keyValuePairs)
    {
        return [
            $this->notWhere($items, $keyValuePairs),
            $this->where($items, $keyValuePairs),
        ];
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
     * @param string $url
     * @return array
     */
    public function urlToFormParameters($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $vars = [];
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
        $ret = [];
        foreach ($values as $key => $value) {
            if (null !== $parent) {
                $keyName = sprintf('%s[%s]', $parent, $key);
            } else {
                $keyName = $key;
            }
            if (is_scalar($value)) {
                $ret[$keyName] = $value;
            } else {
                $ret = array_merge($ret, $this->valuesToFormParameters($value, $keyName));
            }
        }
        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            'trans_form_errors' => new TwigFunction('trans_form_errors', [$this, 'transFormErrors']),
            'embed_params' => new TwigFunction('embed_params', [$this, 'getEmbedParams']),
            'defaults' => new TwigFunction('defaults', [$this, 'getDefaultOf']),
            'embed' => new TwigFunction('embed', [$this, 'embed']),
            'is_granted' => new TwigFunction('is_granted', [$this, 'isGranted']),
            'embedded_image' => new TwigFunction('embedded_image', [$this, 'embeddedImage']),
        ];
    }

    /**
     * Given an existing file, returns an embedded data stream, or null when the file does not exist
     *
     * For example
     * {{ embedded_image('foo.jpg') }}
     *  --> "data:image/jpg;base64,BLABLABLA"
     *
     * @param string $filename
     * @return null|string
     */
    public function embeddedImage($filename)
    {
        if (is_file($filename) && preg_match('/[.](?P<extension>[a-zA-Z0-9]+)$/', $filename, $matches)) {
            return sprintf('data:image/%s;base64,%s', $matches['extension'], base64_encode(file_get_contents($filename)));
        }

        return null;
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
        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }
        return $this->authChecker->isGranted($role, $object);
    }

    /**
     * @param array|object $list
     * @param int $numGroups
     * @return array
     */
    public function groups($list, $numGroups)
    {
        $items = ($list instanceof \Traversable ? iterator_to_array($list) : $list);

        $groups = [];
        $i = 0;
        foreach ($items as $item) {
            $groups[$i++ % $numGroups][] = $item;
        }
        return $groups;
    }

    /**
     * Formats some values as an 'onclick' attribute for Google Analytics
     *
     * @param null $values
     * @return string
     */
    public function gaTrackEvent($values = null)
    {
        $values = func_get_args();
        array_unshift($values, '_trackEvent');

        return sprintf(
            ' onclick="_gaq.push(%s);"',
            htmlspecialchars(json_encode(array_values($values)))
        );
    }

    /**
     * @param array|object $collection
     * @param array $types
     * @return array
     */
    public function sortByType($collection, $types)
    {
        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        // store a map of the original sorting, so the usort can use this to keep the original sorting if the types are
        // equal
        $idToIndexMap = [];
        foreach ($collection as $index => $item) {
            $idToIndexMap[$item->getId()] = $index;
        }

        $numTypes = count($types);

        usort(
            $collection,
            function ($left, $right) use ($types, $idToIndexMap, $numTypes) {
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
            }
        );

        return $collection;
    }

    /**
     * Dash to CamelCase
     *
     * @param string $str
     * @param bool $camelFirst
     * @return string
     */
    public function strDash($str, $camelFirst = true)
    {
        if ($camelFirst) {
            $str = StrUtil::camel($str);
        }
        return StrUtil::dash($str);
    }

    /**
     * CamelCased to under_score
     *
     * @param string $str
     * @param bool $camelFirst
     * @return string
     */
    public function strUscore($str, $camelFirst = true)
    {
        if ($camelFirst) {
            $str = StrUtil::camel($str);
        }
        return StrUtil::uscore($str);
    }

    /**
     * Camelcase
     *
     * @param String $str
     * @return string
     */
    public function strCamel($str)
    {
        return StrUtil::camel($str);
    }

    /**
     * @param string $str
     * @return string
     */
    public function strHumanize($str)
    {
        return StrUtil::humanize($str);
    }

    /**
     * @param string|object|int $date
     * @param string $format
     * @return string
     */
    public function dateFormat($date, $format = '%e %b %Y')
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
            throw new \InvalidArgumentException(sprintf('Cannot format %s as a date', $date));
        }

        return strftime($format, $ts);
    }

    /**
     * @param string|object $date
     * @return string
     */
    public function relativeDate($date)
    {
        $now = new \DateTime();
        $diff = $date->diff($now);
        // natively, diff doesn't contain 'weeks'.
        $diff->w = round($diff->d / 7);
        $message = '';
        foreach (['y', 'm', 'w', 'd', 'h', 'i', 's'] as $part) {
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
                        ['%count%' => $diff->$part]
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
    public function regexReplace($subject, $pattern, $replacement)
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
     * {@inheritDoc}
     */
    public function getNodeVisitors()
    {
        return [
            'zicht_render_add_embed_params' => new RenderAddEmbedParamsNodeVisitor(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return [
            'zicht_switch' => new ControlStructures\SwitchTokenParser(),
            'zicht_strict' => new ControlStructures\StrictTokenParser(),
            'zicht_meta_annotate' => new Meta\AnnotateTokenParser(),
            'zicht_meta_annotations' => new Meta\AnnotationsTokenParser(),
        ];
    }

    /**
     * @return AnnotationRegistry
     */
    public function getAnnotationRegistry()
    {
        return $this->annotationRegistry;
    }

    /**
     * @param array|string $urlOrArray
     * @return array|mixed|string
     */
    public function embed($urlOrArray)
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
            $currentParams = [];
            parse_str($query, $currentParams);
            $currentParams += $embedParams;

            return preg_replace('/\?$/', '', $urlOrArray) . '?' . http_build_query($currentParams);
        } else {
            throw new \InvalidArgumentException('Only supports arrays or strings');
        }
    }

    /**
     * @return array
     */
    public function getEmbedParams()
    {
        return array_filter($this->embedHelper->getEmbedParams());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zicht_framework_extra';
    }

    /**
     * @return mixed|null
     */
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
     * Dumps given variable in styled format
     *
     * @param mixed $var
     * @param string $mode
     * @return mixed
     */
    public function dump($var, $mode = null)
    {
        if (null === $mode && class_exists('Zicht\Util\Debug')) {
            return htmlspecialchars(Debug::dump($var));
        } else {
            switch ($mode) {
                case 'export':
                    VarDumper::dump($var);
                    break;
                default:
                    var_dump($var);
                    break;
            }
        }
        return null;
    }

    /**
     * @param object $data
     * @return string
     */
    public function xml($data)
    {
        if ($data instanceof SimpleXMLElement) {
            $data = $data->saveXML();
        }
        $dom = new DOMDocument();
        $dom->loadXml($data);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /**
     * Strips tags
     *
     * @param string $html
     * @return string
     */
    public function htmlToText($html)
    {
        return strip_tags($html);
    }

    /**
     * @param FormError[] $formErrorList
     * @param string $translationDomain
     * @return array
     */
    public function transFormErrors($formErrorList, $translationDomain)
    {
        $ret = [];
        foreach ($formErrorList as $k => $formError) {
            $ret[$k] = $this->translator->trans($formError->getMessage(), $formError->getMessageParameters(), $translationDomain);
        }
        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function getTests()
    {
        return [
            new TwigTest('numeric', [$this, 'isNumeric']),
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    /**
     * Checks if a given value is numeric
     *
     * @param mixed $value
     * @return boolean
     */
    public function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Checks if a given value is an instance of a certain object
     *
     * @param object $value
     * @param string $instance
     * @return bool
     */
    public function isInstanceof($value, $instance)
    {
        return is_object($value) && $value instanceof $instance;
    }
}
