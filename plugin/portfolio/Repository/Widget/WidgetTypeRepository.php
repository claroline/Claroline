<?php

namespace Icap\PortfolioBundle\Repository\Widget;

use Doctrine\ORM\EntityRepository;

class WidgetTypeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllInArray()
    {
        $query = $this->createQueryBuilder('wt')
            ->select('wt.name', 'wt.icon');

        return $query->getQuery()->getArrayResult();
    }
}
