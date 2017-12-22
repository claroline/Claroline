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
use Doctrine\ORM\Query\ResultSetMapping;
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

    public function findMimeTypesForCalibration()
    {
        $dql = '
            SELECT icon.mimeType FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon icon 
            WHERE icon.mimeType != :mimeType 
            AND icon.mimeType IS NOT NULL 
            AND icon.isShortcut = false 
            GROUP BY icon.mimeType 
            HAVING COUNT(icon.id) > 1
        ';

        return array_column($this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('mimeType', 'custom')
            ->getScalarResult(), 'mimeType');
    }

    public function recalibrateIconItemsForMimeTypes(array $mimeTypes)
    {
        $qb = $this->createQueryBuilder('i');
        $expr = $qb->expr()->in('i.mimeType', $mimeTypes);

        $sql = '
            UPDATE claro_icon_item ii, (
                SELECT MIN(id) AS id, mimeType AS mimeType 
                FROM claro_resource_icon 
                WHERE mimeType IN ('.implode(', ', $expr->getArguments()).')
                AND is_shortcut = :shortcut 
                GROUP BY mimeType) ri
            SET ii.resource_icon_id = ri.id
            WHERE ii.mime_type = ri.mimeType
        ';

        return $this
            ->getEntityManager()
            ->getConnection()
            ->executeUpdate($sql, ['shortcut' => false]);
    }

    public function updateResourceIconsReferenceAfterCalibration(array $mimeTypes)
    {
        $qb = $this->createQueryBuilder('i');
        $expr = $qb->expr()->in('i.mimeType', $mimeTypes);

        $sql = '
            UPDATE claro_resource_node n, claro_resource_icon rin, claro_resource_icon ri
            SET n.icon_id = ri.id
            WHERE n.icon_id = rin.id
            AND rin.mimeType = ri.mimeType
            AND rin.is_shortcut = ri.is_shortcut
            AND (
                ri.id IN (
                  SELECT DISTINCT resource_icon_id FROM claro_icon_item
                  WHERE mime_type IN (' .implode(', ', $expr->getArguments()).')
                ) 
                OR ri.id IN (
                    SELECT DISTINCT ri2.shortcut_id FROM claro_icon_item ii2 
                    INNER JOIN claro_resource_icon ri2 ON ii2.resource_icon_id = ri2.id
                    WHERE ii2.mime_type IN (' .implode(', ', $expr->getArguments()).')
                )
            )
        ';

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

    public function deleteRedundantResourceIconsAfterCalibration(array $mimeTypes)
    {
        $qb = $this->createQueryBuilder('i');
        $expr = $qb->expr()->in('i.mimeType', $mimeTypes);

        $sql = 'SELECT DISTINCT ri2.shortcut_id AS id FROM claro_icon_item ii2 
                INNER JOIN claro_resource_icon ri2 ON ii2.resource_icon_id = ri2.id';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $ids = array_column($this->getEntityManager()->createNativeQuery($sql, $rsm)->getScalarResult(), 'id');
        $sql = '
            DELETE FROM claro_resource_icon
            WHERE mimeType IN (' .implode(', ', $expr->getArguments()).')
            AND id NOT IN (
                SELECT DISTINCT resource_icon_id FROM claro_icon_item
            )
            AND id NOT IN (
                ' .implode(', ', $ids).'
            )
        ';

        return $this->getEntityManager()->getConnection()->executeUpdate($sql, ['shortcut' => false]);
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
