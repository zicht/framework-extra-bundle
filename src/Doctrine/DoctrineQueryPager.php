<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable;

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
     * @var \Doctrine\ORM\Query|null
     */
    protected $countQuery;

    /**
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
     * {@inheritDoc}
     */
    public function getTotal()
    {
        if (!isset($this->countQuery)) {
            $c = clone $this->qb;
            $this->countQuery = $c->select('COUNT(' . $this->alias . ') ' . $this->countAlias)->getQuery();
        }
        return (int)$this->countQuery->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function setRange($start, $length)
    {
        $this->qb->setFirstResult($start)->setMaxResults($length);
    }
}
