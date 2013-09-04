<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable;
use Doctrine\ORM\QueryBuilder;

class DoctrineQueryPager implements Pageable
{
    function __construct(QueryBuilder $q, $alias = 'f')
    {
        $this->qb = $q;
        $this->alias = $alias;
    }


    /**
     * Returns the absolute total of the pageable set of elements.
     *
     * @return int
     */
    function getTotal()
    {
        $c = clone $this->qb;
        $c->select('COUNT(' . $this->alias . ') c');
        return $c->getQuery()->getSingleScalarResult();
    }

    /**
     * Sets the range that needs to be displayed on the current page
     *
     * @param int $start
     * @param int $length
     * @return int
     */
    function setRange($start, $length)
    {
        $this->qb->setFirstResult($start)->setMaxResults($length);
    }
}