<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\NativeQuery;
use Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable;

class NativeQueryPagable implements Pageable
{
    /** @var NativeQuery */
    private $query;

    /** @var Statement */
    private $countQuery;

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
        return $this->countQuery->executeQuery()->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function setRange($start, $length)
    {
        $this->query->setParameter(':limit', (int)$length, \PDO::PARAM_INT);
        $this->query->setParameter(':offset', (int)$start, \PDO::PARAM_INT);
    }
}
