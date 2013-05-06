<?php
/* register the drupal specific tags and filters within a
* proper declared twig extension
*
* Part of the Drupal twig extension distribution
* http://renebakx.nl/twig-for-drupal
*/

class TFD_Extension extends Twig_Extension {

    /* registers the drupal specific tags */
    public function getTokenParsers() {
        $parsers = array();
        $parsers[] = new TFD_TokenParser_FunctionCall('theme');
        $parsers[] = new TFD_TokenParser_FunctionCall('t');
        $parsers[] = new TFD_TokenParser_FunctionCall('l');
        $parsers[] = new TFD_TokenParser_FunctionCall('url');
        $parsers[] = new TFD_TokenParser_With();
        $parsers[] = new TFD_TokenParser_Switch();
        $parsers[] = new TFD_TokenParser_Unset();
        $parsers[] = new TFD_TokenParser_Region();

        return $parsers;
    }

    /* registers the drupal specific filters */
    public function getFilters() {
        $filters = array();
        $filters['replace']         = new Twig_Filter_Function('tfd_str_replace');
        $filters['re_replace']      = new Twig_Filter_Function('tfd_re_replace');
        $filters['dump']            = new Twig_Filter_Function('tfd_dump', array('needs_environment' => true));
        $filters['defaults']        = new Twig_Filter_Function('tfd_defaults_filter');

        $filters['size']            = new Twig_Filter_Function('format_size');
        $filters['url']             = new Twig_Filter_Function('tfd_url');
        $filters['t']               = new Twig_Filter_Function('t');
        $filters['ucfirst']         = new Twig_Filter_Function('ucfirst');
        $filters['strtotime']       = new Twig_Filter_Function('strtotime');

        $filters['imagecache_url']  = new Twig_Filter_Function('tfd_imagecache_url');
        $filters['imagecache_size'] = new Twig_Filter_Function('tfd_imagecache_size');

        $filters['date_format']     = new Twig_Filter_Function('tfd_date_format_filter');
        $filters['fileinfo']        = new Twig_Filter_Function('tfd_fileinfo_filter');
        $filters['nl2br']           = new Twig_Filter_Function('tfd_nl2br_filter');

        $filters['markup']          = new Twig_Filter_Function('check_markup');
        $filters['plain']           = new Twig_Filter_Function('check_plain');
        $filters['to_seconds']      = new Twig_Filter_Function('tfd_to_seconds');
        $filters['fill_zero']       = new Twig_Filter_Function('tfd_fill_zero');
        $filters['files_path']      = new Twig_Filter_Function('tfd_files_path');

        $filters['chomp']           = new Twig_Filter_Function('tfd_chomp');

        $filters = array_merge($filters, module_invoke_all('twig_filters', $filters));
        return $filters;
    }

    /**
     * Implements getFunctions
     *
     * @return array
     */
    public function getFunctions() {
        $functions = array();
        $functions = array_merge($functions, module_invoke_all('twig_functions', $functions));
        return $functions;
    }

    /**
     * maps &&/|| to AND/OR operators for more php like syntax
     * @return <array>   Returns a list of operators to add to the existing list.
     */
    public function getOperators() {
        return array(
            //unaryOperators
            array(

            ),
            // binaryOperators
            array(
                '||'     => array('precedence' => 10, 'class' => 'Twig_Node_Expression_Binary_Or', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
                '&&'     => array('precedence' => 15, 'class' => 'Twig_Node_Expression_Binary_And', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT)
            ),
        );

    }

    public function getTests() {
        $ret = array();
        $ret['property'] = new Twig_Test_Function('tfd_property_test');
        $ret['module_enabled'] = new Twig_Test_Function('tfd_module_enabled_test');
        return $ret;
    }

    public function getName() {
        return 'drupal';
    }

    public function getGlobals() {
        $ret = array();
        $ret = array_merge($ret, module_invoke_all('twig_globals', $ret));
        if (!isset($ret['time'])) {
            $ret['time'] = time();
        }
        return $ret;
    }

}

// ------------------------------------------------------------------------------------------------
// the above declared filter implementations go here

/**
 * Twig filter for str_replace, switches needle and arguments to provide sensible
 * filter arguments order
 *
 * {{ haystack|replace("needle", "replacement") }}
 *
 * @param  $haystack
 * @param  $needle
 * @param  $repl
 * @return mixed
 */
function tfd_str_replace($haystack, $needle, $repl) {
    return str_replace($needle, $repl, $haystack);
}

function tfd_re_replace($haystack, $needle, $repl) {
    return preg_replace($needle, $repl, $haystack);
}


function tfd_path_to_theme() {
    return base_path() . path_to_theme();
}


function tfd_date_format_filter($timestamp, $format = '%d-%m-%Y %H:%M', $mode = 'strftime') {
    switch($mode) {
        case 'strftime':
        case 'date':
            return $mode($format, $timestamp);
            break;
        default:
            throw new InvalidArgumentException($mode .' is not a valid date_format mode');
    }
}


function tfd_defaults_filter($value, $defaults = null) {
    $args = func_get_args();
    $args = array_filter($args);
    if(count($args)) {
        return array_shift($args);
    } else {
        return null;
    }
}


function tfd_dump($env, $var, $function = null) {
    static $functions = array('dpr' => null, 'dpm' => null, 'print_r' => 'p', 'var_dump' => 'v');

    if(empty($function)) {
        if(module_exists('devel')) {
            $function = array_shift(array_keys($functions));
        } else {
            $function = array_pop(array_keys($functions));
        }
    }

    if(array_key_exists($function, $functions) && is_callable($function)) {
        call_user_func($function, $var);
    } else {
        $found = false;
        foreach($functions as $name => $alias) {
            if(in_array($function, (array)$alias)) {
                $found = true;
                call_user_func($name, $var);
                break;
            }
        }
        if(!$found) {
            throw new InvalidArgumentException("Invalid mode '$function' for TFD_dump()");
        }
    }
}


function tfd_imagecache_url($filepath, $preset = null) {
    if(is_array($filepath)) {
        $filepath = $filepath['filepath'];
    }
    if($preset) {
        return imagecache_create_url($preset, $filepath);
    } else {
        return $filepath;
    }
}


function tfd_imagecache_size($filepath, $preset, $asHtml = true) {
    if(is_array($filepath)) {
        $filepath= $filepath['filepath'];
    }
    $info = image_get_info(imagecache_create_path($preset, $filepath));
    $attr = array('width' => (string)$info['width'], 'height' => (string)$info['height']);
    if($asHtml) {
        return drupal_attributes($attr);
    } else {
        return $attr;
    }
}


function tfd_url($item, $options = array()) {
    if(is_numeric($item)) {
        $ret = url('node/' . $item, (array) $options);
    } else {
        $ret = url($item, (array) $options);
    }
    return check_url($ret);
}


function tfd_property_test($element, $propertyName, $value = true) {
    return array_key_exists("#$propertyName", $element) && $element["#$propertyName"] == $value;
}

function tfd_module_enabled_test($name) {
    return module_exists($name);
}

function tfd_fileinfo_filter($file, $type = null) {
    $ret = null;
    if(is_array($file)) {
        $file = $file['filepath'];
    }
    switch($type) {
        case 'ext':
            $type = 'extension';
        case 'extension':
        case 'filename':
        case 'basename':
            $properties = pathinfo($file);
            $ret = $properties[$type];
            break;
        case 'size':
            $type = 'filesize';
        case 'filesize':
        case 'filemtime':
            $ret = $type($file);
            break;
    }

    return $ret;
}


function tfd_nl2br_filter($value) {
    return preg_replace('/^.*$/m', '$0<br>', $value);
}


function tfd_to_seconds($title, $minute, $seconds) {
    $time = ($minute * 60) + $seconds;
    return $time;
}

function tfd_fill_zero($value, $digits) {
    $value = (string) $value;

    if (strlen($value) < $digits) {
        $diff = $digits - strlen($value);
        $zeroes = str_repeat("0", $diff);
        $value = $zeroes . $value;
    }

    return $value;
}


function tfd_files_path($value, $nid) {
    return str_replace('files/', 'files/' . $nid . '
    /
', $value);
}

function tfd_chomp($value, $length, $suffix = ' (..)') {
    $len = strlen($value);
    if ($len <= $length) {
        return $value;
    } else {
        return substr($value, 0, $length) . ' ' . $suffix;
    }
}