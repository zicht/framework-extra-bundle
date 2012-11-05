<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Tests;

use Symfony\Component\DependencyInjection\Container;

use \Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractIntegrationTestCase extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    static $container = null;
    static $testParams = array();

    static function setUpBeforeClass() {
//         $params = parse_ini_file(__DIR__.'/../../assets/test-parameters.ini', true);
//         if($params && !empty($params[APPLICATION_ENV])) {
//             self::$testParams = $params[APPLICATION_ENV];
//         }
        self::$container = new Container();
    }


    /**
     * @var \Sro\Service\Sro
     */
    protected $service;

    function setUp() {
        $this->service = self::$container->get('sro');
    }


    protected function requireTestParams() {
        if(empty(self::$testParams)) {
            $this->markTestSkipped('Need test parameters for this test');
        }
    }
}