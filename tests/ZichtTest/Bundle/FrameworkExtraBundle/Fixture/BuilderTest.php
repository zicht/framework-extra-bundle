<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\FrameworkExtraBundle\Fixture {
    use Zicht\Bundle\FrameworkExtraBundle\Fixture\Builder;

    class BuilderTest extends \PHPUnit_Framework_TestCase
    {
        public $builder;

        function setUp()
        {
            $this->builder = Builder::create(array('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets'));
        }

        function testBuilderCallsSetterOnEnd()
        {
            $a = $this->builder->A()->B()->end()->peek();

            $this->assertTrue(count($a->b) == 1);
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\B', $a->b[0]);
        }


        function testBuilderCallsParentSetterOnEnd()
        {
            $a = $this->builder->A()->B()->end()->peek();

            list($b) = $a->b;
            $this->assertSame($a, $b->a);
        }


        function testBuilderCallsChildAdder()
        {
            $a = $this->builder->A()->A()->end()->peek();
            $this->assertGreaterThan(0, count($a->children));
            list($child) = $a->children;
            $this->assertNotSame($a, $child);
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\A', $child);
        }

        function testCustomSetter()
        {
            $a = $this->builder->A()->B()->end('customSetter')->peek();
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\B', $a->customSet);
        }


        function testBuilderCallsSetParentIfSameClass()
        {
            $a = $this->builder->A()->A()->end()->peek();
            $this->assertGreaterThan(0, count($a->children));
            list($child) = $a->children;
            $this->assertNotSame($child->parent, $child);
            $this->assertSame($child->parent, $a);
        }



        function testCallerIsAlwaysCalled()
        {
            $calls = array();
            $this->builder->always(function($obj) use(&$calls) {
                $calls[spl_object_hash($obj)]= $obj;
            });
            $this->builder->A()->A()->end()->end();
            $this->assertEquals(2, count($calls));
        }


        function testMethodCall() {
            $a = $this->builder->A()->setSomething('foo')->peek();
            $this->assertEquals('foo', $a->getSomething());
        }


        /**
         * @expectedException \BadMethodCallException
         */
        function testBadMethodCall() {
            $this->builder->A()->setSomethingElse('foo');
        }


        /**
         * @expectedException \UnexpectedValueException
         */
        function testEmptyStack() {
            $this->builder->A()->end()->end();
        }

        /**
         * @expectedException \UnexpectedValueException
         */
        function testEmptyStackPeek() {
            $this->builder->A()->end()->peek();
        }
    }
}

namespace ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets {
    class A {
        public $b;
        public $children;
        public $parent;
        public $something;

        function addB(B $b) {
            $this->b[]= $b;
        }


        function addChildren(A $a) {
            $this->children[]= $a;
        }


        function setParent(A $a) {
            $this->parent = $a;
        }


        function customSetter(B $b) {
            $this->customSet = $b;
        }


        function setSomething($something) {
            $this->something = $something;
        }


        function getSomething() {
            return $this->something;
        }
    }

    class B {
        public $a;

        function setA(A $a) {
            $this->a = $a;
        }
    }
}
