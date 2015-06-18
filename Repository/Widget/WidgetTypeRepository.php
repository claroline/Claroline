<?php

namespace Icap\PortfolioBundle\Repository\Widget;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class WidgetTypeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllInArray()
    {
        $query = $this->createQueryBuilder('wt')
            ->select('wt.name', 'wt.isUnique', 'wt.icon');

        return $query->getQuery()->getArrayResult();
    }
}