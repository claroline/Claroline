<?php

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    public function deleteNotificationsBeforeDate(\DateTime $date)
    {
        $qb = $this->createQueryBuilder("notification");
        $qb
            ->delete()
            ->andWhere("notification.creationDate < :limitDate")
            ->setParameter("limitDate", $date);

        $qb->getQuery()->execute();
    }
}