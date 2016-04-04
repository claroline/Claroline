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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FieldFacetRoleRepository extends EntityRepository
{
    public function findByRoles(array $roles)
    {
        //the mighty admin can do anything in our world
        if (in_array('ROLE_ADMIN', $roles)) {
            $dql = "SELECT
                field.id as id,
                field.position as position,
                1 as canOpen,
                1 as canEdit
                FROM Claroline\CoreBundle\Entity\Facet\FieldFacet field
            ";
        } else{
            $dql = "
                SELECT
                  field.id as id,
                  field.position as position,
                  MAX(ffr.canOpen) as canOpen,
                  MAX(ffr.canEdit) as canEdit
                FROM Claroline\CoreBundle\Entity\Facet\FieldFacetRole ffr
                JOIN ffr.role role
                JOIN ffr.fieldFacet field
                WHERE role.name IN (:rolenames)
                GROUP BY field.id
           ";
        }

        $query = $this->_em->createQuery($dql);

        if (!in_array('ROLE_ADMIN', $roles)) {
        $query->setParameter('rolenames', $roles);
        }

        return $query->getArrayResult();
    }
} 