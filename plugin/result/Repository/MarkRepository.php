<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\ResultBundle\Entity\Result;
use Doctrine\ORM\EntityRepository;

class MarkRepository extends EntityRepository
{
    /**
     * Returns an array representation of the marks associated
     * with a given result.
     *
     * @param Result $result
     *
     * @return array
     */
    public function findByResult(Result $result)
    {
        $dql = '
            SELECT
                u.id,
                CONCAT(u.firstName, \' \', u.lastName) AS name,
                m.value AS mark,
                m.id AS markId
            FROM Claroline\ResultBundle\Entity\Mark m
            JOIN m.user u
            WHERE m.result = :result
            ORDER BY u.lastName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('result', $result);

        return $query->getArrayResult();
    }

    /**
     * Returns an array representation of the mark associated
     * with a result for a given user.
     *
     * @param Result $result
     * @param User   $user
     *
     * @return array
     */
    public function findByResultAndUser(Result $result, User $user)
    {
        $dql = '
            SELECT
                u.id,
                CONCAT(u.firstName, \' \', u.lastName) AS name,
                m.value AS mark,
                m.id AS markId
            FROM Claroline\ResultBundle\Entity\Mark m
            JOIN m.user u
            WHERE m.result = :result
            AND u = :user
            ORDER BY u.lastName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'result' => $result,
            'user' => $user,
        ]);

        return $query->getArrayResult();
    }
}
