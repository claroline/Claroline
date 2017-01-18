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

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FacetRepository extends EntityRepository
{
    public function findVisibleFacets(TokenInterface $token, $max = null)
    {
        $roleNames = [];

        foreach ($token->getRoles() as $role) {
            $roleNames[] = $role->getRole();
        }

        //the mighty admin can do anything in our world
        if (in_array('ROLE_ADMIN', $roleNames)) {
            $dql = "
            SELECT facet FROM Claroline\CoreBundle\Entity\Facet\Facet facet
            ORDER BY facet.isMain, facet.position
        ";
            $query = $this->_em->createQuery($dql);
        } else {
            $dql = "
            SELECT facet FROM Claroline\CoreBundle\Entity\Facet\Facet facet
            JOIN facet.roles role
            WHERE role.name IN (:rolenames)
            ORDER BY facet.isMain, facet.position
        ";

            $query = $this->_em->createQuery($dql);
            $query->setParameter('rolenames', $roleNames);
        }
        if ($max !== null) {
            $query->setMaxResults($max);
        }

        return $query->getResult();
    }

    public function findByUser(User $user, $showAll = false)
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.panelFacets', 'pf')
            ->leftJoin('pf.fieldsFacet', 'ff')
            ->leftJoin('ff.fieldsFacetValue', 'ffv');

        if (!$showAll) {
            $qb
               ->join('f.roles', 'frole')
               ->join('pf.panelFacetsRole', 'pfr')
               ->andWhere('frole in (:roles)')
               ->andWhere('pfr.role in (:roles)')
               ->andWhere('pfr.canOpen = true')
               ->setParameter('roles', $user->getEntityRoles());
        }

        return $qb->getQuery()->getResult();
    }

    public function countFacets($isMain = false)
    {
        $isMain = !is_bool($isMain) ? $isMain === 'true' : $isMain;
        $dql = '
            SELECT COUNT(facet) FROM Claroline\CoreBundle\Entity\Facet\Facet facet
            WHERE facet.isMain = :isMain
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('isMain', $isMain);

        return $query->getSingleScalarResult();
    }
}
