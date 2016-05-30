<?php

namespace Claroline\ForumBundle\Repository;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityRepository;

class LastMessageWidgetConfigRepository extends EntityRepository
{
    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneOrNullByWidgetInstance(WidgetInstance $widgetInstance)
    {
        return $this->createQueryBuilder('lastMessageWidgetConfig')
            ->where('lastMessageWidgetConfig.widgetInstance = :widgetInstance')
            ->setParameter('widgetInstance', $widgetInstance)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
