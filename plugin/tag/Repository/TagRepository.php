<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function findOnePlatformTagByName($name)
    {
        return $this->_em
            ->createQuery('
                SELECT t
                FROM Claroline\TagBundle\Entity\Tag t
                WHERE t.user IS NULL
                AND t.name = :name
            ')
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function findOneUserTagByName(User $user, $name)
    {
        return $this->_em
            ->createQuery('
                SELECT t
                FROM Claroline\TagBundle\Entity\Tag t
                WHERE t.user = :user
                AND t.name = :name
            ')
            ->setParameters([
                'user' => $user,
                'name' => $name,
            ])
            ->getOneOrNullResult();
    }
}
