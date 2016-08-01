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

use Claroline\CoreBundle\Entity\User;
use Claroline\ScormBundle\Entity\Scorm2004Resource;
use Doctrine\ORM\EntityRepository;

class Scorm2004ScoTrackingRepository extends EntityRepository
{
    public function findTrackingsByResource(Scorm2004Resource $resource)
    {
        $dql = '
            SELECT t
            FROM Claroline\ScormBundle\Entity\Scorm2004ScoTracking t
            JOIN t.user u
            JOIN t.sco s
            WHERE s.scormResource = :resource
            ORDER BY u.lastName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('resource', $resource);

        return $query->getResult();
    }

    public function findAllTrackingsByUserAndResource(User $user, Scorm2004Resource $resource)
    {
        $dql = '
            SELECT t
            FROM Claroline\ScormBundle\Entity\Scorm2004ScoTracking t
            JOIN t.sco s
            WHERE t.user = :user
            AND s.scormResource = :resource
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('resource', $resource);

        return $query->getResult();
    }
}
