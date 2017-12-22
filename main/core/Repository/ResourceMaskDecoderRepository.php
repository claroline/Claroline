<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ResourceMaskDecoderRepository extends EntityRepository
{
    public function removeMasksByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('mask')
            ->delete()
            ->where('mask.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->execute();
    }

    public function findDuplicateMasksIds()
    {
        $sql = 'SELECT DISTINCT mask2.id FROM claro_resource_mask_decoder AS mask1,
                claro_resource_mask_decoder AS mask2
                WHERE mask2.id > mask1.id AND mask2.resource_type_id = mask1.resource_type_id
                AND mask2.value = mask1.value AND mask2.name = mask1.name';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $query = $this->_em->createNativeQuery($sql, $rsm);

        return array_column($query->getArrayResult(), 'id');
    }
}
