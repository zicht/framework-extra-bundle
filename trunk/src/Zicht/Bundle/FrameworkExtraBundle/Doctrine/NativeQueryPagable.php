<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;


class NativeQueryPagable implements \Zicht\Bundle\FrameworkExtraBundle\Pager\Pageable
{
    public function __construct(\Doctrine\ORM\NativeQuery $queryWrapper, \Doctrine\DBAL\Statement $countQuery)
    {
        $this->query = $queryWrapper;
        $this->countQuery = $countQuery;
    }

    /**
     * @{inheritDoc}
     */
    function getTotal()
    {
        $this->countQuery->execute();
        return $this->countQuery->fetch(\Pdo::FETCH_COLUMN);
    }

    /**
     * @{inheritDoc}
     */
    function setRange($start, $length)
    {
        $this->query->setParameter(':limit', (int) $length, \Pdo::PARAM_INT);
        $this->query->setParameter(':offset', (int) $start, \Pdo::PARAM_INT);
    }
}
