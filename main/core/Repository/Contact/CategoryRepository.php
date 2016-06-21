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

class CategoryRepository extends EntityRepository
{
    public function findCategoriesByUser(
        User $user,
        $orderedBy = 'order',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Category c
            WHERE c.user = :user
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCategoryByUserAndName(User $user, $name, $executeQuery = true)
    {
        $dql = '
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Category c
            WHERE c.user = :user
            AND c.name = :name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('name', $name);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findOrderOfLastCategoryByUser(User $user)
    {
        $dql = '
            SELECT MAX(c.order) AS order_max
            FROM Claroline\CoreBundle\Entity\Contact\Category c
            WHERE c.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }
}
