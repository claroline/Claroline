<?php

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class NotificationViewerRepository extends EntityRepository
{
    public function findUserNotificationsQuery($viewerId, $visibleTypes, $category = null)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->join('notificationViewer.notification', 'notification')
            ->andWhere('notificationViewer.viewerId = :viewerId')
            ->orderBy('notification.creationDate', 'DESC')
            ->setParameter('viewerId', $viewerId);
        $this->addVisibleTypesRestriction($queryBuilder, $visibleTypes);
        $this->addCategoryRestriction($queryBuilder, $category);

        return $queryBuilder->getQuery();
    }

    public function markAsViewed($notificationViewIds)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->update()
            ->set('notificationViewer.status', true)
            ->andWhere($queryBuilder->expr()->in('notificationViewer.id', $notificationViewIds));

        $queryBuilder->getQuery()->execute();
    }

    public function markAllAsViewed($userId)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->update()
            ->set('notificationViewer.status', true)
            ->andWhere('notificationViewer.viewerId = :viewerId')
            ->setParameter('viewerId', $userId);

        $queryBuilder->getQuery()->execute();
    }

    public function countUnviewedNotifications($userId, $visibleTypes)
    {
        $queryBuilder = $this->createQueryBuilder('notificationViewer');
        $queryBuilder
            ->select('COUNT(notificationViewer.id) AS total')
            ->join('notificationViewer.notification', 'notification')
            ->andWhere('notificationViewer.viewerId = :viewerId')
            ->andWhere($queryBuilder->expr()->neq('notificationViewer.status', ':viewed'))
            ->setParameter('viewed', true)
            ->setParameter('viewerId', $userId);
        $this->addVisibleTypesRestriction($queryBuilder, $visibleTypes);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    private function addVisibleTypesRestriction($qb, $visibleTypes)
    {
        if (count($visibleTypes) > 0) {
            foreach ($visibleTypes as $name => $val) {
                if (!$val) {
                    $qb->andWhere(
                        $qb
                            ->expr()
                            ->notLike(
                                'notification.actionKey',
                                $qb->expr()->literal('%'.$name.'%')
                            )
                    );
                }
            }
        }
    }

    private function addCategoryRestriction(QueryBuilder $qb, $category)
    {
        if ($category != null) {
            if ($category != 'system') {
                $qb->andWhere('notification.iconKey = :category')
                    ->setParameter('category', $category);
            } else {
                $qb->andWhere(
                  $qb->expr()->isNull('notification.iconKey')
                );
            }
        }
    }
}
