<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Tests;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    static $testParams = array();

    protected function requireTestParams()
    {
        if (empty(self::$testParams)) {
            $this->markTestSkipped('Need test parameters for this test');
        }
    }
}