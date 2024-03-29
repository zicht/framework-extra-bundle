<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Fixture;

use Zicht\Util\Str;

/**
 * Fixture builder helper class. Provides a fluent interface for building fixture objects in Doctrine ORM.
 */
class Builder
{
    /** @var array */
    private $namespaces;

    private $stack = [];

    private $alwaysDo = [];

    /**
     * Constructor, initializes the builder object. To use the builder, call Builder::create(...)
     *
     * @param string $namespaces
     */
    private function __construct($namespaces)
    {
        $this->namespaces = (array)$namespaces;
    }

    /**
     * Creates a builder for the specified namespace
     *
     * @param string|array $namespaces
     * @return Builder
     */
    public static function create($namespaces)
    {
        return new self($namespaces);
    }

    /**
     * Adds a method call to all fixture objects, typically array($manager, 'persist')
     *
     * @param callable $do
     * @return Builder
     */
    public function always($do)
    {
        $this->alwaysDo[] = $do;
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
        if ($this->current() && method_exists($this->current(), $method)) {
            call_user_func_array([$this->current(), $method], $args);
        } else {
            $entity = $method;

            $className = $this->resolve($entity);
            if ($className) {
                $class = new \ReflectionClass($className);
                if ($args) {
                    $entityInstance = $class->newInstanceArgs($args);
                } else {
                    $entityInstance = $class->newInstance();
                }
                $this->push($entityInstance);
            } else {
                throw new \BadMethodCallException("No class found for {$entity} in [" . join(', ', $this->namespaces) . ']' . ($this->current() ? ', nor is it a method in ' . get_class($this->current()) : ''));
            }
        }
        return $this;
    }

    /**
     * Resolve the entity name to any of the configured namespaces.
     * Returns null if not found.
     *
     * @param string $entity
     * @return string|null
     */
    private function resolve($entity)
    {
        foreach ($this->namespaces as $namespace) {
            $className = $namespace . '\\' . ucfirst($entity);
            if (class_exists($className)) {
                return $className;
            }
        }
        return null;
    }

    /**
     * Returns the top of the stack.
     *
     * @return mixed
     */
    protected function current()
    {
        if (count($this->stack)) {
            return $this->stack[count($this->stack) - 1];
        }
        return null;
    }

    /**
     * Pushes an object onto the stack
     *
     * @param object $entity
     */
    protected function push($entity)
    {
        $this->stack[] = $entity;
    }

    /**
     * Returns one level up in the tree.
     *
     * @param null $setter
     * @return Builder
     */
    public function end($setter = null)
    {
        if (!count($this->stack)) {
            throw new \UnexpectedValueException('Stack is empty. Did you call end() too many times?');
        }
        $current = array_pop($this->stack);
        if ($parent = $this->current()) {
            $parentClassName = get_class($parent);
            $entityLocalName = Str::classname(get_class($current));

            if ($current instanceof $parentClassName) {
                if (method_exists($parent, 'addChildren')) {
                    call_user_func([$parent, 'addChildren'], $current);
                }
            }
            if (is_null($setter)) {
                foreach (['set', 'add'] as $methodPrefix) {
                    $methodName = $methodPrefix . $entityLocalName;

                    if (method_exists($parent, $methodName)) {
                        $setter = $methodName;
                        break;
                    }
                }
            }
            if (!is_null($setter)) {
                call_user_func([$parent, $setter], $current);
            }

            $parentClassNames = array_merge(class_parents($parentClassName), [$parentClassName]);

            foreach (array_reverse($parentClassNames) as $lParentClassName) {
                $lParentClass = Str::classname($lParentClassName);
                $parentSetter = 'set' . $lParentClass;
                if ($lParentClassName == get_class($current)) {
                    $parentSetter = 'setParent';
                }
                if (method_exists($current, $parentSetter)) {
                    call_user_func(
                        [$current, $parentSetter],
                        $parent
                    );
                    break;
                }
            }
            foreach ($this->alwaysDo as $callable) {
                call_user_func($callable, $parent);
            }
        }
        foreach ($this->alwaysDo as $callable) {
            call_user_func($callable, $current);
        }
        return $this;
    }

    /**
     * Returns the object that is currently the subject of building
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function peek()
    {
        $c = count($this->stack);
        if ($c == 0) {
            throw new \UnexpectedValueException('The stack is empty. You should probably peek() before the last end() call.');
        }
        $ret = $this->stack[$c - 1];
        return $ret;
    }
}
