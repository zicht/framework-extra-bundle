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
        $filters = $this->extension->getFilters();
        $this->assertArrayHasKey('re_replace', $filters);
        $this->assertArrayHasKey('dump', $filters);
        $this->assertArrayHasKey('truncate', $filters);
        $this->assertArrayHasKey('str_uscore', $filters);
        $this->assertArrayHasKey('str_dash', $filters);
        $this->assertArrayHasKey('str_camel', $filters);
        $this->assertArrayHasKey('date_format', $filters);
        $this->assertArrayHasKey('relative_date', $filters);
        $this->assertArrayHasKey('ga_trackevent', $filters);
        $this->assertArrayHasKey('with', $filters);
        $this->assertArrayHasKey('without', $filters);
        $this->assertArrayHasKey('round', $filters);
        $this->assertArrayHasKey('ceil', $filters);
        $this->assertArrayHasKey('floor', $filters);
    }

    /**
     * @dataProvider filterData
     */
    function testFilters($filter, $args, $expect)
    {
        $filters = $this->extension->getFilters();
        $this->assertEquals($expect, call_user_func_array($filters[$filter]->getCallable(), $args));
    }



    function filterData() {
        return array(
            array('re_replace', array('ab', '/.b/', 'c'), 'c'),
            array('truncate', array('abc def', 4), 'abc ...'),
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
        );
    }

}