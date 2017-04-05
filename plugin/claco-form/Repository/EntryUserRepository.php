<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Repository;

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class EntryUserRepository extends EntityRepository
{
    public function findSharedEntryUserByClacoFormAndUser(ClacoForm $clacoForm, User $user)
    {
        $dql = '
            SELECT eu
            FROM Claroline\ClacoFormBundle\Entity\EntryUser eu
            JOIN eu.entry e
            JOIN e.clacoForm c
            WHERE c = :clacoForm
            AND eu.user = :user
            AND eu.shared = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSharedEntriesUsersByClacoForm(ClacoForm $clacoForm)
    {
        $dql = '
            SELECT eu
            FROM Claroline\ClacoFormBundle\Entity\EntryUser eu
            JOIN eu.entry e
            JOIN e.clacoForm c
            WHERE c = :clacoForm
            AND eu.shared = true
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);

        return $query->getResult();
    }
}
