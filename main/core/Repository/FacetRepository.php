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
use Symfony\Component\Security\Core\Role\Role;

class FacetRepository extends EntityRepository
{
    public function findVisibleFacets(TokenInterface $token, $isRegistration = false)
    {
        // retrieves current user roles
        $roleNames = array_map(function (Role $role) {
            return $role->getRole();
        }, $token->getRoles());

        $qb = $this->createQueryBuilder('f');
        if (!in_array('ROLE_ADMIN', $roleNames)) {
            // filter query to only get accessible facets for the current roles
            $qb
                ->join('f.roles', 'r')
                ->where('r.name IN (:roles)')
                ->setParameter('roles', $roleNames);
        }

        if ($isRegistration) {
            $qb->andWhere('f.forceCreationForm = true');
        }

        $qb->orderBy('f.main, f.position');

        return $qb->getQuery()->getResult();
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

    /**
     * @deprecated
     *
     * @param bool $isMain
     *
     * @return mixed
     */
    public function countFacets($isMain = false)
    {
        $isMain = !is_bool($isMain) ? $isMain === 'true' : $isMain;
        $dql = '
            SELECT COUNT(facet) FROM Claroline\CoreBundle\Entity\Facet\Facet facet
            WHERE facet.main = :isMain
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('isMain', $isMain);

        return $query->getSingleScalarResult();
    }
}
