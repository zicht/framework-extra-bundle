<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Util;

use IteratorAggregate;

/**
 * List maintaining sort order.
 *
 * @deprecated Use sorting functionality from `zicht/itertools` instead
 */
class SortedList implements IteratorAggregate
{
    /** @var array */
    protected $items = [];

    /**
     * Add an item with the specified priority.
     *
     * @param mixed $item
     * @param int $priority
     * @return void
     */
    public function insert($item, $priority)
    {
        $this->items[] = [$priority, $item];

        $this->sort();
    }


    /**
     * @return void
     */
    public function sort()
    {
        usort(
            $this->items,
            function ($a, $b) {
                if ($a[0] === $b[0]) {
                    return 0;
                }
                return $a[0] < $b[0] ? -1 : 1;
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator(
            array_map(
                function ($item) {
                    return $item[1];
                },
                $this->items
            )
        );
    }
}
