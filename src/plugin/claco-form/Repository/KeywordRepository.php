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
use Doctrine\ORM\EntityRepository;

class KeywordRepository extends EntityRepository
{
    public function findKeywordByName(ClacoForm $clacoForm, $name)
    {
        $dql = '
            SELECT k
            FROM Claroline\ClacoFormBundle\Entity\Keyword k
            JOIN k.clacoForm c
            WHERE c = :clacoForm
            AND UPPER(k.name) = :name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $upperName = strtoupper($name);
        $query->setParameter('name', $upperName);

        return $query->getOneOrNullResult();
    }

    public function findKeywordByNameExcludingUuid(ClacoForm $clacoForm, $name, ?string $uuid = null)
    {
        $dql = '
            SELECT k
            FROM Claroline\ClacoFormBundle\Entity\Keyword k
            JOIN k.clacoForm c
            WHERE c = :clacoForm
            AND UPPER(k.name) = :name
        ';

        if (!empty($uuid)) {
            $dql .= ' AND k.uuid != :uuid';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('name', strtoupper($name));
        if (!empty($uuid)) {
            $query->setParameter('uuid', $uuid);
        }

        return $query->getOneOrNullResult();
    }
}
