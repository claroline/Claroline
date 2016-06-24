<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 6/9/16
 */

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Innova\PathBundle\Entity\Path\Path;

class StepRepository extends EntityRepository
{
    /**
     * Counts total published steps for path.
     *
     * @param Path $path
     *
     * @return int
     */
    public function countForPath(Path $path)
    {
        $qb = $this->createQueryBuilder('step');
        $qb
            ->select('COUNT(step.id) AS total')
            ->andWhere('step.path = :path')
            ->setParameter('path', $path);

        return intval($qb->getQuery()->getSingleScalarResult());
    }
}
