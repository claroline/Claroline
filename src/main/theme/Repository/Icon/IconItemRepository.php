<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/16/17
 */

namespace Claroline\ThemeBundle\Repository\Icon;

use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class IconItemRepository extends EntityRepository
{
    public function findIconsForResourceIconSetByMimeTypes(
        IconSet $iconSet = null,
        $excludeMimeTypes = null,
        $includeMimeTypes = null
    ) {
        $qb = $this->createQueryBuilder('icon')->select('icon');
        if (is_null($iconSet)) {
            $this->addDefaultResourceIconSetToQueryBuilder($qb);
        } else {
            $qb->andWhere('icon.iconSet = :iconSet')
                ->setParameter('iconSet', $iconSet);
        }

        if (!empty($excludeMimeTypes)) {
            $qb->andWhere($qb->expr()->notIn('icon.mimeType', $excludeMimeTypes));
        } elseif (!empty($includeMimeTypes)) {
            $qb->andWhere($qb->expr()->in('icon.mimeType', $includeMimeTypes));
        }

        return $qb->getQuery()->getResult();
    }

    private function addDefaultResourceIconSetToQueryBuilder(QueryBuilder $qb)
    {
        $qb->join('icon.iconSet', 'st')
            ->andWhere('st.default = :isDefault')
            ->andWhere('st.cname = :defaultCname')
            ->setParameter('isDefault', true)
            ->setParameter('defaultCname', 'claroline');
    }
}
