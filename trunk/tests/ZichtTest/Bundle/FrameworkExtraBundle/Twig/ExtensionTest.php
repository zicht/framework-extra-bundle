<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\FrameworkExtraBundle\Twig;

/**
 * @covers Zicht\Bundle\FrameworkExtraBundle\Twig\Extension
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Extension
     */
    protected $extension;

    protected function setUp()
    {
        parent::setUp();

        $this->embedHelper = $this->getMockBuilder('Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper')->disableOriginalConstructor()->getMock();
        $this->annotationRegistry = $this->getMockBuilder('Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry')->getMock();

        $this->extension = new \Zicht\Bundle\FrameworkExtraBundle\Twig\Extension(
            $this->embedHelper,
            $this->annotationRegistry
        );
    }


    /**
     * @covers Zicht\Bundle\FrameworkExtraBundle\Twig\Extension::getFilters
     */
    function testAvailableFilters()
    {
        $this->getFilter('re_replace');
        $this->getFilter('dump');
        $this->getFilter('truncate');
        $this->getFilter('str_uscore');
        $this->getFilter('str_dash');
        $this->getFilter('str_camel');
        $this->getFilter('date_format');
        $this->getFilter('relative_date');
        $this->getFilter('ga_trackevent');
        $this->getFilter('with');
        $this->getFilter('without');
        $this->getFilter('round');
        $this->getFilter('ceil');
        $this->getFilter('floor');
    }

    /**
     * @dataProvider filterData
     */
    function testFilters($filter, $args, $expect)
    {
        $filter = $this->getFilter($filter);
        $this->assertEquals($expect, call_user_func_array($filter->getCallable(), $args));
    }



    function getFilter($filterName)
    {
        foreach ($this->extension->getFilters() as $k => $filter) {
            if ($filter->getName() == $filterName) {
                return $filter;
            }
        }
        throw new \OutOfBoundsException("{$filterName} not found");
    }



    function filterData() {
        return array(
            array('re_replace', array('ab', '/.b/', 'c'), 'c'),
            array('truncate', array('abc def', 4), 'abc...'),
            array('truncate', array('abc def', 4, ' ...'), 'abc ...'),
            array('str_uscore', array('abcDef'), \Zicht\Util\Str::uscore('abcDef')),
            array('str_uscore', array('abc def'), \Zicht\Util\Str::uscore('abc def')),
            array('str_dash', array('abcDef'), \Zicht\Util\Str::dash('abcDef')),
            array('str_dash', array('abc def'), \Zicht\Util\Str::dash('abc def')),
            array('relative_date', array(new \DateTime('-10 seconds -2 minutes')), '2 minutes ago'),
            array('relative_date', array(new \DateTime('-1 hour')), '1 hour ago'),
            array('date_format', array(new \DateTime(), 'ymd'), 'ymd'),
            array('date_format', array(new \DateTime(), '%Y-%m-%d'), strftime('%Y-%m-%d')),
            array('floor', array(1.9), 1),
            array('floor', array(1.1), 1),
            array('ceil', array(1.1), 2),
            array('ceil', array(1.8), 2),
            array('round', array(1.4), 1),
            array('round', array(1.6), 2),
            array('url_to_form_params', array(''), array()),
            array('url_to_form_params', array('/'), array()),
            array('url_to_form_params', array('/?'), array()),
            array('url_to_form_params', array('/?a'), array('a' => '')), //< --- this is intended behaviour
            array('url_to_form_params', array('/?a=b'), array('a' => 'b')),
            array('url_to_form_params', array('/?a[x][y]=b'), array('a[x][y]' => 'b')),
            array('url_to_form_params', array('/?a[x][y]=b&c=1'), array('a[x][y]' => 'b', 'c' => '1')),
            array('url_to_form_params', array('/?a[x][y]=b&a[x][z]=c'), array('a[x][y]' => 'b', 'a[x][z]' => 'c')),

            array('url_strip_query', array(''), ''),
            array('url_strip_query', array('/'), '/'),
            array('url_strip_query', array('/?'), '/'),
            array('url_strip_query', array('/?a'), '/'),
            array('url_strip_query', array('/?a=b'), '/'),
            array('url_strip_query', array('/?a[x][y]=b'), '/'),
            array('url_strip_query', array('/?a[x][y]=b&c=1'), '/'),
            array('url_strip_query', array('/?a[x][y]=b&a[x][z]=c'), '/'),
            array('url_strip_query', array('http://www.example.org/some/path/with=values?a[x][y]=b&a[x][z]=c'), 'http://www.example.org/some/path/with=values'),
            array('url_strip_query', array('https://www.example.org/some/path/with=values?a[x][y]=b&a[x][z]=c'), 'https://www.example.org/some/path/with=values'),
            array('url_strip_query', array('https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'), 'https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'),
            array('url_strip_query', array('https://www.example.com/some/path/with=values?a[x][y]=b&a[x][z]=c#note_that_the_hash_should_remain'), 'https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'),
        );
    }
}