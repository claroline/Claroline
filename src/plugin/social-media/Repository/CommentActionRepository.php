<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/7/15
 */

namespace Icap\SocialmediaBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class CommentActionRepository extends EntityRepository
{
    public function findCommentsForPagination($resourceId)
    {
        $qb = $this->createQueryBuilder('commentAction');
        $qb
            ->select('commentAction')
            ->andWhere('commentAction.resource = :resource')
            ->setParameter('resource', $resourceId)
            ->orderBy('commentAction.creationDate', 'DESC');

        return $qb;
    }

    public function removeComment($commentId, User $user)
    {
        $qb = $this->createQueryBuilder('commentAction');
        $qb
            ->delete("\Icap\SocialmediaBundle\Entity\CommentAction", 'commentAction')
            ->andWhere('commentAction.id = :id')
            ->andWhere('commentAction.user = :user')
            ->setParameter('id', $commentId)
            ->setParameter('user', $user);

        $qb->getQuery()->execute();
    }

    public function findHasCommentedUserIds($resourceId)
    {
        $qb = $this->createQueryBuilder('commentAction');

        $qb
            ->select('usr.id')
            ->distinct(true)
            ->innerJoin('commentAction.user', 'usr')
            ->andWhere('commentAction.resource = :resource')
            ->setParameter('resource', $resourceId);

        return $qb->getQuery()->getArrayResult();
    }
}
