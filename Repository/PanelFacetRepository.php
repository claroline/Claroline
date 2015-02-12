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
}
