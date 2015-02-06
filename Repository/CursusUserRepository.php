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
    public function findOneCursusUserByCursusAndUser(
        Cursus $cursus,
        User $user,
        $executeQuery = true
    )
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
}
