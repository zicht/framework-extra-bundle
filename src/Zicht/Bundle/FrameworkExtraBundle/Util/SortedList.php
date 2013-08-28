<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Util;

use IteratorAggregate;

class SortedList implements IteratorAggregate
{
    protected $items = array();

    public function insert($item, $priority)
    {
        $this->items[]= array($priority, $item);

        $this->sort();
    }


    public function sort()
    {
        usort($this->items, function($a, $b) {
            return $a[0] > $b[0] ? 1 : $a[0] === $b[0] ? 0 : -1;
        });
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_map(function($item) { return $item[1]; }, $this->items));
    }
}