<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Fixture;

/**
 * Fixture builder helper class. Provides a fluent interface for building fixture objects in Doctrine ORM.
 */
class Builder
{
    /**
     * Creates a builder for the specified namespace
     *
     * @param string $namespace
     * @return Builder
     */
    static function create($namespace)
    {
        return new self($namespace);
    }


    /**
     * Constructor, initializes the builder object. To use the builder, call Builder::create(...)
     *
     * @param string $namespace
     */
    private function __construct($namespace)
    {
        $this->namespace = $namespace;
        $this->stack    = array();
        $this->alwaysDo = array();
    }


    /**
     * Adds a method call to all fixture objects, typically array($manager, 'persist')
     *
     * @param callable $do
     * @return Builder
     */
    public function always($do)
    {
        $this->alwaysDo[]= $do;
        return $this;
    }


    /**
     * Implements the builder / fluent interface for building fixture objects.
     *
     * @param string $method
     * @param array $args
     * @return Builder
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (method_exists($this->current(), $method)) {
            call_user_func_array(array($this->current(), $method), $args);
        } else {
            $entity = $method;

            $className = $this->namespace . '\\' . ucfirst($entity);
            if (class_exists($className)) {
                $class = new \ReflectionClass($className);

                $entityInstance = $class->newInstanceArgs($args);

                if ($parent = $this->current()) {
                    foreach ($this->alwaysDo as $callable) {
                        call_user_func($callable, $parent);
                    }

                    $parentClass = @array_pop(explode('\\', get_class($parent)));


                    foreach (array('set', 'add') as $methodPrefix) {
                        if ($parentClass == ucfirst($entity)) {
                            $methodName = $methodPrefix . 'Child';
                        } else {
                            $methodName = $methodPrefix . ucfirst($entity);
                        }

                        if (method_exists($parent, $methodName)) {
                            call_user_func(array($parent, $methodName), $entityInstance);
                            break;
                        }
                    }
                    $parentSetter = 'set' . $parentClass;
                    if ($parentClass == ucfirst($entity)) {
                        $parentSetter = 'setParent';
                    }
                    if (method_exists($entityInstance, $parentSetter)) {
                        call_user_func(
                            array($entityInstance, $parentSetter),
                            $parent
                        );
                    }
                }

                $this->push($entityInstance);
            } else {
                throw new \BadMethodCallException("{$className} does not exist");
            }
        }
        return $this;
    }


    /**
     * Returns the top of the stack.
     *
     * @return mixed
     */
    protected function current()
    {
        if (count($this->stack)) {
            return $this->stack[count($this->stack) -1];
        }
        return null;
    }


    /**
     * Pushes an object onto the stack
     *
     * @param $entity
     */
    protected function push($entity)
    {
        $this->stack[]= $entity;
    }


    /**
     * Returns one level up in the tree.
     *
     * @return Builder
     */
    public function end()
    {
        $top = array_pop($this->stack);
        foreach ($this->alwaysDo as $callable) {
            call_user_func($callable, $top);
        }
        return $this;
    }
}