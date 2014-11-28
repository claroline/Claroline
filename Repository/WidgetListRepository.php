<?php

namespace Icap\BlogBundle\Repository;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityRepository;
use Icap\BlogBundle\Entity\WidgetList;

class WidgetListRepository extends EntityRepository
{
    /**
     * @param WidgetInstance|Blog $widgetInstance
     * @param bool                $executeQuery
     *
     * @return WidgetList[]|\Doctrine\ORM\AbstractQuery
     */
    public function findByWidgetInstance(WidgetInstance $widgetInstance, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT wl, b, rn, bo, p
                FROM IcapBlogBundle:WidgetList wl
                JOIN wl.blog b
                JOIN b.resourceNode rn
                JOIN b.options bo
                LEFT JOIN b.posts p
                WHERE wl.widgetInstance = :widgetInstance
            ')
            ->setParameter('widgetInstance', $widgetInstance)
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}
