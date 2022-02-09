<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\User;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\EntityRepository;

class OrganizationRepository extends EntityRepository
{
    /**
     * @return Organization[]
     */
    public function findByMember(string $userUuid)
    {
        return $this->createQueryBuilder('o')
            ->join('o.userOrganizationReferences', 'r')
            ->join('r.user', 'u')
            ->where('u.uuid = :uuid')
            ->setParameters([
                'uuid' => $userUuid,
            ])
            ->getQuery()
            ->getResult();
    }
}
