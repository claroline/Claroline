<?php

namespace Icap\BlogBundle\Repository;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityRepository;
use Icap\BlogBundle\Entity\WidgetList;

class WidgetListBlogRepository extends EntityRepository
{
    /**
     * @param WidgetInstance|Blog $widgetInstance
     * @param bool                $executeQuery
     *
     * @return WidgetList[]|\Doctrine\ORM\AbstractQuery
     */
    public function findByWidgetInstance(WidgetInstance $widgetInstance, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('wl')
            ->select(array('wl', 'resourceNode'))
            ->join('wl.resourceNode', 'resourceNode')
            ->where('wl.widgetInstance = :widgetInstance')
            ->setParameter('widgetInstance', $widgetInstance)
            ->getQuery()
        ;

        return $executeQuery ? $query->getResult() : $query;
    }
}
