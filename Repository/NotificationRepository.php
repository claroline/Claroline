<?php

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    public function findUserNotificationsQuery($viewerId, $max)
    {
        $queryBuilder = $this->createQueryBuilder('notification');
        $queryBuilder
            ->join('Icap\NotificationBundle\Entity\NotificationViewer', 'notificationViewer', 'WITH', 'notification = notificationViewer.notification')
            ->andWhere('notificationViewer.viewerId = :viewerId')
            ->orderBy('notification.creationDate', 'DESC')
            ->setParameter("viewerId", $viewerId);

        if (!empty($max)) {
            $queryBuilder->setMaxResults($max);
        }

        return $queryBuilder->getQuery();
    }

    public function findUserLatestNotifications ($viewerId, $max)
    {
        $query = $this->findUserLatestNotifications($viewerId, $max);

        return $query->getArrayResult();
    }
}