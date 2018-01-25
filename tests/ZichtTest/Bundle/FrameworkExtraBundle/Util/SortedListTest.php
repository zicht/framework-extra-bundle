<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Util;

use Zicht\Bundle\FrameworkExtraBundle\Util\SortedList;

class SortedListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test handling of priorities by SortedList.
     */
    public function testSortedListPriority()
    {
        $s = new SortedList();

        $item1 = new \stdClass();
        $item1->item1 = true;

        $item2 = new \stdClass();
        $item2->item2 = true;

        // add item1 with priority 9, it should be the first element.
        $s->insert($item1, 9);
        $s->insert($item2, 10);

        $elements = iterator_to_array($s);
        $this->assertTrue(property_exists($elements[0], 'item1'));
        $this->assertTrue(property_exists($elements[1], 'item2'));
    }

    /**
     * Test handling of priorities by SortedList
     */
    public function testSortedListPriorityAddedReverseOrder()
    {
        $s = new SortedList();

        $item1 = new \stdClass();
        $item1->item1 = true;

        $item2 = new \stdClass();
        $item2->item2 = true;

        // add item1 with priority 10, it should be the second element even though its added first
        $s->insert($item1, 10);
        $s->insert($item2, 9);

        $elements = iterator_to_array($s);
        $this->assertTrue(property_exists($elements[0], 'item2'));
        $this->assertTrue(property_exists($elements[1], 'item1'));
    }
}
