<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Tests;

use PHPUnit\Framework\TestCase;

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
