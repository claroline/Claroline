<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Repository;

use Doctrine\ORM\EntityRepository;

class Scorm2004ResourceRepository extends EntityRepository
{
    public function getNbScormWithHashName($hashName)
    {
        $dql = '
            SELECT COUNT(s.id)
            FROM Claroline\ScormBundle\Entity\Scorm2004Resource s
            WHERE s.hashName = :hashName
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('hashName', $hashName);

        return $query->getSingleScalarResult();
    }
}
