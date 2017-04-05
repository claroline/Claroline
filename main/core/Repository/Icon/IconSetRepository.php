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

use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Doctrine\ORM\EntityRepository;

class IconSetRepository extends EntityRepository
{
    public function findActiveRepositoryResourceStampIcon()
    {
        $qb = $this->createQueryBuilder('iconset')
            ->select('iconset.resourceStampIcon AS stampIcon')
            ->where('iconset.active = :active')
            ->andWhere('iconset.type = :type')
            ->setParameter('active', true)
            ->setParameter('type', IconSetTypeEnum::RESOURCE_ICON_SET);
        $res = $qb->getQuery()->getSingleScalarResult();

        return isset($res['stampIcon']) ? $res['stampIcon'] : null;
    }
}
