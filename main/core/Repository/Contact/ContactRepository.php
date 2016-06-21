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
use Claroline\CoreBundle\Entity\Contact\Category;
use Claroline\CoreBundle\Entity\User;

class ContactRepository extends EntityRepository
{
    public function findContactsByUser(
        User $user,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Contact c
            JOIN c.contact cc
            WHERE c.user = :user
            ORDER BY cc.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findContactsByUserAndSearch(
        User $user,
        $search,
        $withUsername = false,
        $withMail = false,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = '
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Contact c
            JOIN c.contact cc
            WHERE c.user = :user
            AND (
                UPPER(cc.firstName) LIKE :search
                OR UPPER(cc.lastName) LIKE :search
                OR CONCAT(UPPER(cc.firstName), CONCAT(\' \', UPPER(cc.lastName))) LIKE :search
                OR CONCAT(UPPER(cc.lastName), CONCAT(\' \', UPPER(cc.firstName))) LIKE :search
        ';

        if ($withUsername) {
            $dql .= '
                OR UPPER(cc.username) LIKE :search
            ';
        }

        if ($withMail) {
            $dql .= '
                OR UPPER(cc.mail) LIKE :search
            ';
        }
        $dql .= '
            )
        ';
        $dql .= "
            ORDER BY cc.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findContactByUserAndContact(
        User $user,
        User $contact,
        $executeQuery = true
    ) {
        $dql = '
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Contact c
            WHERE c.user = :user
            AND c.contact = :contact
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('contact', $contact);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findContactsByUserAndCategory(
        User $user,
        Category $category,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CoreBundle\Entity\Contact\Contact c
            JOIN c.contact cc
            JOIN c.categories cat WITH (cat = :category)
            WHERE c.user = :user
            ORDER BY cc.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('category', $category);

        return $executeQuery ? $query->getResult() : $query;
    }
}
