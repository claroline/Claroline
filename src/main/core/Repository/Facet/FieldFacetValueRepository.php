<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Facet;

use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class FieldFacetValueRepository extends EntityRepository
{
    /**
     * @return FieldFacetValue[]
     */
    public function findPlatformValuesByUser(User $user)
    {
        return $this->_em
            ->createQuery('
                SELECT fv
                FROM Claroline\CoreBundle\Entity\Facet\FieldFacetValue fv
                LEFT JOIN fv.fieldFacet AS f
                WHERE fv.user = :user
                  AND f.panelFacet IS NOT NULL
            ')
            ->setParameter('user', $user)
            ->getResult();
    }
}
