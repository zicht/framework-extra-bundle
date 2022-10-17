<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Fixture {
    use PHPUnit\Framework\TestCase;
    use Zicht\Bundle\FrameworkExtraBundle\Fixture\Builder;

    class BuilderTest extends TestCase
    {
        public $builder;

        public function setUp(): void
        {
            $this->builder = Builder::create(['ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets']);
        }

        public function testBuilderCallsSetterOnEnd()
        {
            $a = $this->builder->A()->B()->end()->peek();

            $this->assertTrue(count($a->b) == 1);
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\B', $a->b[0]);
        }

        public function testBuilderCallsParentSetterOnEnd()
        {
            $a = $this->builder->A()->B()->end()->peek();

            list($b) = $a->b;
            $this->assertSame($a, $b->a);
        }

        public function testBuilderCallsChildAdder()
        {
            $a = $this->builder->A()->A()->end()->peek();
            $this->assertGreaterThan(0, count($a->children));
            list($child) = $a->children;
            $this->assertNotSame($a, $child);
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\A', $child);
        }

        public function testCustomSetter()
        {
            $a = $this->builder->A()->B()->end('customSetter')->peek();
            $this->assertInstanceOf('ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets\B', $a->customSet);
        }

        public function testBuilderCallsSetParentIfSameClass()
        {
            $a = $this->builder->A()->A()->end()->peek();
            $this->assertGreaterThan(0, count($a->children));
            list($child) = $a->children;
            $this->assertNotSame($child->parent, $child);
            $this->assertSame($child->parent, $a);
        }

        public function testCallerIsAlwaysCalled()
        {
            $calls = [];
            $this->builder->always(function ($obj) use (&$calls) {
                $calls[spl_object_hash($obj)] = $obj;
            });
            $this->builder->A()->A()->end()->end();
            $this->assertEquals(2, count($calls));
        }

        public function testMethodCall()
        {
            $a = $this->builder->A()->setSomething('foo')->peek();
            $this->assertEquals('foo', $a->getSomething());
        }

        public function testBadMethodCall()
        {
            $this->expectException('\BadMethodCallException');
            $this->builder->A()->setSomethingElse('foo');
        }

        public function testEmptyStack()
        {
            $this->expectException('\UnexpectedValueException');
            $this->builder->A()->end()->end();
        }

        public function testEmptyStackPeek()
        {
            $this->expectException('\UnexpectedValueException');
            $this->builder->A()->end()->peek();
        }
    }
}

namespace ZichtTest\Bundle\FrameworkExtraBundle\Fixture\Assets {
    class A
    {
        public $b;

        public $children;

        public $parent;

        public $something;

        public function addB(B $b)
        {
            $this->b[] = $b;
        }

        public function addChildren(A $a)
        {
            $this->children[] = $a;
        }

        public function setParent(A $a)
        {
            $this->parent = $a;
        }

        public function customSetter(B $b)
        {
            $this->customSet = $b;
        }

        public function setSomething($something)
        {
            $this->something = $something;
        }

        public function getSomething()
        {
            return $this->something;
        }
    }

    class B
    {
        public $a;

        public function setA(A $a)
        {
            $this->a = $a;
        }
    }
}
