<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractIntegrationTestCase extends TestCase
{
    /** @var array */
    protected static $testParams = [];

    protected function requireTestParams()
    {
        if (empty(self::$testParams)) {
            $this->markTestSkipped('Need test parameters for this test');
        }
    }
}
