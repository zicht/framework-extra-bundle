<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Pager;

/**
 * Contract for pageable elements
 */
interface Pageable
{
    /**
     * Returns the absolute total of the pageable set of elements.
     *
     * @return int
     */
    public function getTotal();


    /**
     * Sets the range that needs to be displayed on the current page
     *
     * @param int $start
     * @param int $length
     */
    public function setRange($start, $length);
}
