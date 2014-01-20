<?php

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NotificationViewerRepository extends EntityRepository
{
    public function findUserNotificationsQuery($viewerId, $max = null)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->join('notificationViewer.notification', 'notification')
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

    public function markAsViewed ($notificationViewIds)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->update()
            ->set('notificationViewer.status', true)
            ->andWhere($queryBuilder->expr()->in('notificationViewer.id', $notificationViewIds));

        $queryBuilder->getQuery()->execute();
    }

    public function countUnviewedNotifications ($userId)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->select('COUNT(notificationViewer.id) AS total')
            ->andWhere('notificationViewer.viewerId = :viewerId')
            ->andWhere($queryBuilder->expr()->neq('notificationViewer.status', ':viewed'))
            ->setParameter('viewed', true)
            ->setParameter('viewerId', $userId);

        return $queryBuilder->getQuery()->getSingleResult();
    }
}