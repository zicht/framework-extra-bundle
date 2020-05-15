<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Pdo;
use Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable;
use Doctrine\ORM\NativeQuery;
use Doctrine\DBAL\Statement;

class NativeQueryPagable implements Pageable
{
    /**
     * @param NativeQuery $queryWrapper
     * @param Statement $countQuery
     */
    public function __construct(NativeQuery $queryWrapper, Statement $countQuery)
    {
        $this->query = $queryWrapper;
        $this->countQuery = $countQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotal()
    {
        $this->countQuery->execute();
        return $this->countQuery->fetch(Pdo::FETCH_COLUMN);
    }

    /**
     * {@inheritDoc}
     */
    public function setRange($start, $length)
    {
        $this->query->setParameter(':limit', (int)$length, Pdo::PARAM_INT);
        $this->query->setParameter(':offset', (int)$start, Pdo::PARAM_INT);
    }
}
