<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Pager;

/**
 * Pager implementation to handle paging over a countable set of elements
 */
class Pager implements \Iterator, \ArrayAccess, \Countable
{
    /** @var int */
    private $currentPage = -1;

    /** @var int|null */
    private $total;

    /** @var int */
    private $numPages = -1;

    /** @var int */
    private $offset = -1;

    /** @var int */
    private $lengthOfRange = -1;

    /** @var int */
    private $itemsPerPage = -1;

    /** @var Pageable */
    private $results;

    /**
     * @var int|null Used for iterator implementation
     */
    private $ptr;

    /**
     * Constructs the pager with the given set of elements to page over, and the given amount of items per page.
     *
     * @param int $itemsPerPage
     */
    public function __construct(Pageable $pagable, $itemsPerPage)
    {
        $this->currentPage = -1;
        $this->total = null;
        $this->numPages = -1;
        $this->offset = -1;
        $this->lengthOfRange = -1;
        $this->itemsPerPage = -1;
        $this->results = null;

        $this->results = $pagable;
        $this->setItemsPerPage($itemsPerPage);
    }

    /**
     * Sets the maximum number of items per page
     *
     * @param int $itemsPerPage
     * @return void
     * @throws \InvalidArgumentException if the number of items is not a valid non-negative integer
     */
    public function setItemsPerPage($itemsPerPage)
    {
        if ($itemsPerPage <= 0) {
            throw new \InvalidArgumentException('Number of items per page must be positive integer');
        }
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Set the current page index, which is 0-index based.
     * (The first page is 0)
     *
     * If the page index format is invalid, an InvalidArgumentException is thrown.
     * If the page index is out of range, it is trimmed to the nearest logical value; e.g. -1 is interpreted as 0,
     * 15 is interpreted as 7 if the number of pages is 8.
     *
     * @param int $page
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setCurrentPage($page)
    {
        if (is_null($this->total)) {
            $this->total = (int)$this->results->getTotal();
        }
        if ((int)$page != $page) {
            throw new \InvalidArgumentException('Invalid argument $page, expected integer number, got ' . gettype($page));
        }
        $this->numPages = (int)ceil($this->total / $this->itemsPerPage);
        $this->currentPage = min(max(0, $this->getLast()), max($this->getFirst(), $page));

        $this->offset = $this->itemsPerPage * $this->currentPage;
        $this->lengthOfRange = $this->itemsPerPage;

        if ($this->offset + $this->lengthOfRange > $this->total) {
            $this->lengthOfRange = max(0, $this->total - $this->offset);
        }

        $this->results->setRange($this->offset, $this->lengthOfRange);
    }

    /**
     * Returns the first page index
     *
     * @return int
     */
    public function getFirst()
    {
        return 0;
    }

    /**
     * Returns the last page index
     *
     * @return int
     */
    public function getLast()
    {
        return $this->numPages - 1;
    }

    /**
     * Returns the 1-indexed start of the displayed range, used for displaying in templates
     *
     * @return int
     */
    public function getRangeStart()
    {
        return $this->offset + 1;
    }

    /**
     * Returns the 1-indexed end of the displayed range, used for displaying in templates
     *
     * @return int
     */
    public function getRangeEnd()
    {
        return $this->offset + $this->lengthOfRange;
    }

    /**
     * Combines the getRangeStart() and getRangeEnd() in one array
     *
     * @return array
     */
    public function getRange()
    {
        return [$this->getRangeStart(), $this->getRangeEnd()];
    }

    /**
     * Returns the total of entire pageable set
     *
     * @return int|null
     */
    public function getItemTotal()
    {
        return $this->total;
    }

    /**
     * Returns whether the current page has a previous. This is only true for pages past the first.
     *
     * @return bool
     */
    public function hasPrevious()
    {
        return $this->currentPage > $this->getFirst();
    }

    /**
     * Returns whether the current page has a next. This is only true for pages before the last
     *
     * @return bool
     */
    public function hasNext()
    {
        return $this->offsetExists($this->currentPage + 1);
    }

    /**
     * Returns a set of meta information on the current page.
     * See itemAt() for the available information
     *
     * @return array
     */
    public function getCurrent()
    {
        return $this->itemAt($this->currentPage);
    }

    /**
     * Returns the meta data for the next page, and 'null' if there is none
     *
     * @return array|null
     */
    public function getNext()
    {
        if ($this->hasNext()) {
            return $this->itemAt($this->currentPage + 1);
        }
        return null;
    }

    /**
     * Returns the meta data for the previous page, and 'null' if there is none
     *
     * @return array|null
     */
    public function getPrevious()
    {
        if ($this->hasPrevious()) {
            return $this->itemAt($this->currentPage - 1);
        }
        return null;
    }

    /**
     * Meta data helper function, returns the following meta data for each of the requested pages.
     *
     * - title: The displayable title for the current page (e.g. "1" for page 0)
     * - is_previous: Whether the page is the previous page
     * - is_current
     * - is_next: Whether the page is the next page
     *
     * @param int $i
     * @return array
     */
    private function itemAt($i)
    {
        return [
            'index' => $i,
            'title' => $i + 1,
            'is_previous' => $i == ($this->currentPage - 1),
            'is_current' => $i == $this->currentPage,
            'is_next' => $i == ($this->currentPage + 1),
        ];
    }

    /**
     * Iterator::current() implementation
     * Returns the meta data for the current item in the iterator.
     *
     * @return array
     */
    public function current()
    {
        return $this->itemAt($this->ptr);
    }

    /**
     * Iterator::next() implementation, advances the iterator one item.
     *
     * @return void
     */
    public function next()
    {
        ++$this->ptr;
    }

    /**
     * Returns the key of the current Iterator item, which is the page index.
     *
     * @return int
     */
    public function key()
    {
        return $this->ptr;
    }

    /**
     * Iterator::valid() implementation, checks if the current iterator index is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->ptr);
    }

    /**
     * Iterator::rewind() implementation; rewinds the iterator to the start of the range
     *
     * @return void
     */
    public function rewind()
    {
        $this->ptr = $this->getFirst();
    }

    /**
     * ArrayAccess:offsetExists() implementation, checks if the given page index is valid.
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return is_int($offset) && $offset >= $this->getFirst() && $offset <= $this->getLast();
    }

    /**
     * ArrayAccess::offsetGet() implementation; returns the meta data for the given page index, and null
     * if it does not exist.
     *
     * @param int $offset
     * @return array
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->itemAt($offset);
        }
        return null;
    }

    /**
     * ArrayAccess::offsetSet() implementation, throws an exception as the page set is read only
     *
     * @param int $offset
     * @param mixed $value
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(__CLASS__ . ' is read only');
    }

    /**
     * ArrayAccess::offsetUnset() implementation, throws an exception as the page set is read only
     *
     * @param int $offset
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(__CLASS__ . ' is read only');
    }

    /**
     * Countable::count() implementation; Returns the number of pages in the page set.
     *
     * @return int
     */
    public function count()
    {
        return $this->numPages;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $surround
     * @return array
     */
    public function withGaps($surround = 2)
    {
        $ret = [];
        $isPreviousGap = false;
        for ($i = 0; $i < $this->numPages; ++$i) {
            if (($i >= $this->currentPage - $surround && $i <= $this->currentPage + $surround)
                || ($i < $this->getFirst() + $surround)
                || ($i > $this->getLast() - $surround)
            ) {
                $ret[$i] = $this->itemAt($i);
                $isPreviousGap = false;
            } elseif (!$isPreviousGap) {
                $isPreviousGap = true;
                $ret[$i] = null;
            }
        }

        return $ret;
    }

    /**
     * @return int
     */
    public function getCurrentPageIndex()
    {
        return $this->currentPage;
    }
}
