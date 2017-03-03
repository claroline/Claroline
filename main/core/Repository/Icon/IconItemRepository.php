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

namespace Claroline\CoreBundle\Repository\Icon;

use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
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

    public function deleteAllByMimeType($mimeType)
    {
        $qb = $this->createQueryBuilder('icon')
            ->delete()
            ->where('icon.mimeType = :mimeType')
            ->setParameter('mimeType', $mimeType);

        return $qb->getQuery()->getResult();
    }

    public function updateResourceIconsByIconSetIcons(IconSet $iconSet = null, $mimeTypes = null)
    {
        $nativeQuery = 'UPDATE claro_resource_icon ri, claro_icon_item ii
              SET ri.relative_url = ii.relative_url
              WHERE ri.id = ii.resource_icon_id
              AND ii.icon_set_id = :id';
        $params = [];
        if (is_null($iconSet)) {
            $params['id'] = 1;
        } else {
            $params['id'] = $iconSet->getId();
        }

        if (!empty($mimeTypes)) {
            $qb = $this->createQueryBuilder('i');
            $expr = $qb->expr()->in('i.mimeType', $mimeTypes);

            $nativeQuery .= '
                AND ii.mime_type IN ('.implode(', ', $expr->getArguments()).')
            ';
        }

        return $this->getEntityManager()->getConnection()->executeUpdate(
            $nativeQuery,
            $params
        );
    }

    public function updateResourceIconForAllSets(ResourceIcon $icon)
    {
        $qb = $this->createQueryBuilder('icon')
            ->update()
            ->set('icon.resourceIcon', ':icon')
            ->where('icon.mimeType = :mimeType')
            ->setParameter('icon', $icon)
            ->setParameter('mimeType', $icon->getMimeType());
        $qb->andWhere($qb->expr()->isNotNull('icon.resourceIcon'));

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
