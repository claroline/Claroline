<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceModelRepository extends EntityRepository
{
    public function findModelsByUser(User $user)
    {
        $dql = "
            SELECT DISTINCT wm
            FROM Claroline\CoreBundle\Entity\Model\WorkspaceModel wm
            JOIN wm.users u
            WHERE u = :user
            ORDER BY wm.name ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}
