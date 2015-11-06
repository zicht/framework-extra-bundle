<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Util;

/**
 * A sorted set implementation:
 *
 * All values are ensured unique.
 * All values are ensured sorted.
 */
class SortedSet implements \Countable
{
    /**
     * The set values
     *
     * @var array
     */
    private $values;


    /**
     * Construct the set with initial values
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $this->setValues($values);
    }


    /**
     * Remove all current values and replace them with the values.
     *
     * @param \Traversable $values
     * @return void
     */
    public function setValues($values)
    {
        $this->values = array();
        $this->addValues($values);
    }


    /**
     * Returns the values as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }


    /**
     * Add all values in the given array.
     * Values already in the set are ignored, and the set is sorted after adding.
     *
     * @param \Traversable $values
     * @return void
     */
    public function addValues($values)
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }


    /**
     * Checks if the set contains the value
     *
     * @param scalar $value
     * @return array
     */
    public function contains($value)
    {
        return in_array($value, $this->values);
    }


    /**
     * Adds a value to the set.
     * If the value is already present, it is ignored
     *
     * @param scalar $value
     * @return void
     */
    public function add($value)
    {
        $this->values[] = $value;
        $this->stateChanged();
    }


    /**
     * Removes a value from the set, if present
     *
     * @param scalar $value
     * @return void
     */
    public function remove($value)
    {
        foreach ($this->values as $i => $v) {
            if ($value == $v) {
                unset($this->values[$i]);
                break;
            }
        }
        $this->stateChanged();
    }


    /**
     * Returns the number of items in the set
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->values);
    }


    /**
     * Ensured uniqueness and sorted values
     *
     * @return void
     */
    private function stateChanged()
    {
        if (count($this->values)) {
            $this->values = array_unique(array_values($this->values));
            sort($this->values);
        }
    }
}