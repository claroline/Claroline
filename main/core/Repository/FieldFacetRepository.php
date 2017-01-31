<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FieldFacetRepository extends EntityRepository
{
    public function findByRoles(array $roles)
    {
        $dql = '
            SELECT ff
            FROM Claroline\CoreBundle\Entity\Facet\FieldFacet ff
            JOIN ff.panelFacet panel
            JOIN panel.panelFacetsRole pfr
            WHERE pfr.canOpen = true
            AND pfr.role in (:roles)
        ';

        $query = $this->_em->createQuery($dql);

        $query->setParameter('roles', $roles);
        $res = $query->getResult();

        return $res;
    }

    public function findPlatformFieldFacets()
    {
        $dql = '
            SELECT ff
            FROM Claroline\CoreBundle\Entity\Facet\FieldFacet ff
            WHERE ff.resourceNode IS NULL
        ';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
