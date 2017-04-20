<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Cursus;
use Doctrine\ORM\EntityRepository;

class CursusUserRepository extends EntityRepository
{
    public function findCursusUsersByCursus(Cursus $cursus, $executeQuery = true)
    {
        $dql = '
            SELECT cu
            FROM Claroline\CursusBundle\Entity\CursusUser cu
            WHERE cu.cursus = :cursus
            ORDER BY cu.registrationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneCursusUserByCursusAndUser(Cursus $cursus, User $user, $executeQuery = true)
    {
        $dql = '
            SELECT cu
            FROM Claroline\CursusBundle\Entity\CursusUser cu
            WHERE cu.cursus = :cursus
            AND cu.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findCursusUsersFromCursusAndUsers(array $cursus, array $users)
    {
        $dql = '
            SELECT cu
            FROM Claroline\CursusBundle\Entity\CursusUser cu
            WHERE cu.cursus IN (:cursus)
            AND cu.user IN (:users)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('users', $users);

        return $query->getResult();
    }

    public function findCursusUsersOfCursusChildren(Cursus $cursus, User $user, $executeQuery = true)
    {
        $dql = '
            SELECT cu
            FROM Claroline\CursusBundle\Entity\CursusUser cu
            JOIN cu.cursus c
            WHERE cu.user = :user
            AND c.parent = :cursus
            AND c.root = :root
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('root', $cursus->getRoot());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredUsersByCursus(
        Cursus $cursus,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
            AND NOT EXISTS (
                SELECT cu
                FROM Claroline\CursusBundle\Entity\CursusUser cu
                WHERE cu.cursus = :cursus
                AND cu.user = u
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredUsersByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
            AND
            (
                UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.username) LIKE :search
            )
            AND NOT EXISTS (
                SELECT cu
                FROM Claroline\CursusBundle\Entity\CursusUser cu
                WHERE cu.cursus = :cursus
                AND cu.user = u
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }
}
