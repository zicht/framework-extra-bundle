<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable;
use Doctrine\ORM\QueryBuilder;

/**
 * Pageable for doctrine DQL queries
 */
class DoctrineQueryPager implements Pageable
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $countAlias;

    /**
     * @var null
     */
    protected $countQuery;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\QueryBuilder $q
     * @param string $alias
     * @param string $countAlias
     */
    public function __construct(QueryBuilder $q, $alias = 'f', $countAlias = '__count')
    {
        $this->qb = $q;
        $this->alias = $alias;
        $this->countAlias = $countAlias;
        $this->countQuery = null;
    }


    /**
     * Set the count query to override the default generated count query.
     *
     * @param \Doctrine\ORM\Query $countQuery
     * @return void
     */
    public function setCountQuery($countQuery)
    {
        $this->countQuery = $countQuery;
    }


    /**
     * Returns the absolute total of the pageable set of elements.
     *
     * @return int
     */
    public function getTotal()
    {
        if (!isset($this->countQuery)) {
            $c = clone $this->qb;
            $this->countQuery = $c->select('COUNT(' . $this->alias . ') ' . $this->countAlias)->getQuery();
        }
        return $this->countQuery->getSingleScalarResult();
    }

    /**
     * Sets the range that needs to be displayed on the current page
     *
     * @param int $start
     * @param int $length
     * @return int
     */
    public function setRange($start, $length)
    {
        $this->qb->setFirstResult($start)->setMaxResults($length);
    }
}
