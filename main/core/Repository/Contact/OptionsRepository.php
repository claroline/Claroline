<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Contact;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class OptionsRepository extends EntityRepository
{
    public function findOptionsByUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT o
            FROM Claroline\CoreBundle\Entity\Contact\Options o
            WHERE o.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
