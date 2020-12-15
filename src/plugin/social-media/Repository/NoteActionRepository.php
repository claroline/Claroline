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

class NoteActionRepository extends EntityRepository
{
    public function findNotesForPagination($resourceId, User $user)
    {
        $qb = $this->createQueryBuilder('noteAction');
        $qb
            ->select('noteAction')
            ->andWhere('noteAction.resource = :resource')
            ->andWhere('noteAction.user = :user')
            ->setParameter('resource', $resourceId)
            ->setParameter('user', $user)
            ->orderBy('noteAction.creationDate', 'DESC');

        return $qb;
    }

    public function removeNote($noteId, User $user)
    {
        $qb = $this->createQueryBuilder('noteAction');
        $qb
            ->delete("\Icap\SocialmediaBundle\Entity\NoteAction", 'noteAction')
            ->andWhere('noteAction.id = :id')
            ->andWhere('noteAction.user = :user')
            ->setParameter('id', $noteId)
            ->setParameter('user', $user);

        $qb->getQuery()->execute();
    }
}
