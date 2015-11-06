<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Util;

/**
 * A key based map of sorted sets.
 */
class SortedSetMap
{
    private $values = null;

    /**
     * Construct the set based on an array containing typically string keys and array values, where the
     * values correspond to unique parameter values per key.
     *
     * @param array $values
     */
    public function __construct($values = array())
    {
        $this->setValues($values);
    }


    /**
     * Import all values from the array into the map, replacing all current values
     *
     * @param array $values
     * @return void
     */
    public function setValues($values)
    {
        $this->values = array();
        foreach ($values as $key => $value) {
            $this->replace($key, (array)$value);
        }
    }


    /**
     * Add a value to the given map key.
     *
     * @param string $key
     * @param scalar $value
     * @return void
     */
    public function add($key, $value)
    {
        if (!isset($this->values[$key])) {
            $this->values[$key] = new SortedSet();
        }
        $this->values[$key]->add($value);
        $this->stateChanged();
    }


    /**
     * Replaces the map key with the specified set of values.
     *
     * @param string $key
     * @param array $values
     * @return void
     */
    public function replace($key, $values)
    {
        $this->values[$key] = new SortedSet($values);
        $this->stateChanged();
    }


    /**
     * Returns the set of values associated with the given key as an array.
     * Returns an empty array if the key is not present.
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key]->toArray();
        }

        return array();
    }


    /**
     * Checks if a value is associated with the given key.
     *
     * @param string $key
     * @param scalar $value
     * @return bool
     */
    public function contains($key, $value)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key]->contains($value);
        }

        return false;
    }


    /**
     * Checks if the given key is present in the map.
     *
     * @param string $key
     * @return bool
     */
    public function containsKey($key)
    {
        return isset($this->values[$key]);
    }


    /**
     * Removes the given value from the map associated with the given key.
     *
     * @param string $key
     * @param scalar $value
     * @return void
     */
    public function remove($key, $value)
    {
        if (isset($this->values[$key])) {
            $this->values[$key]->remove($value);
        }
        $this->stateChanged();
    }


    /**
     * Removes an entire set of values associated with the given key.
     *
     * @param string $key
     * @return void
     */
    public function removeKey($key)
    {
        if (isset($this->values[$key])) {
            unset($this->values[$key]);
        }
        $this->stateChanged();
    }


    /**
     * Merges a set of values into the given key's set.
     *
     * @param string $key
     * @param Traversable $values
     * @return void
     */
    public function merge($key, $values)
    {
        foreach ((array)$values as $value) {
            $this->add($key, $value);
        }
        $this->stateChanged();
    }


    /**
     * Merge an entire map into the current map.
     *
     * @param array $values
     * @return void
     */
    public function mergeAll(array $values)
    {
        foreach ($values as $key => $value) {
            $this->merge($key, $value);
        }
    }


    /**
     * Returns the map as an array, with all values representing the set of
     * values associated with that key as an array
     *
     * @return array
     */
    public function toArray()
    {
        $ret = array();
        foreach (array_keys($this->values) as $key) {
            $ret[$key] = $this->get($key);
        }

        return $ret;
    }


    /**
     * Ensures all empty sets are removed, and sorts the sets by key name.
     *
     * @return void
     */
    private function stateChanged()
    {
        $keys = array_keys($this->values);
        foreach ($keys as $key) {
            if (!count($this->values[$key])) {
                unset($this->values[$key]);
            }
        }
        ksort($this->values);
    }

    /**
     * Returns the number of values in the set.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->values);
    }

    /**
     * Implements the __clone magic method to clone internal SortedSets
     *
     * @return void
     */
    public function __clone()
    {
        foreach (array_keys($this->values) as $key) {
            $this->values[$key] = clone $this->values[$key];
        }
    }
}
