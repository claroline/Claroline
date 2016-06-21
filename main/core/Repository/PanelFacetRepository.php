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
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\User;

class PanelFacetRepository extends EntityRepository
{
    public function findPanelsAfter(PanelFacet $panel)
    {
        $dql = '
            SELECT pf
            FROM Claroline\CoreBundle\Entity\Facet\PanelFacet pf
            WHERE pf.facet = :facetId
            AND pf.position > :position
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('facetId', $panel->getFacet()->getId());
        $query->setParameter('position', $panel->getPosition());

        return $query->getResult();
    }

    public function findByUser(User $user)
    {
        $dql = '
            SELECT pf
            FROM Claroline\CoreBundle\Entity\Facet\PanelFacet pf
            JOIN pf.panelFacetsRole pfr
            JOIN pfr.role r
            JOIN pf.facet f
            JOIN f.frole
            WHERE (r.name in (:roles) AND pfr.isVisible = true)
            AND frole.name : (:roles)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $user->getRoles());

        return $query->getResult();
    }
}
