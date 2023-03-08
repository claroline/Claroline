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

use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\EntityRepository;

class PluginRepository extends EntityRepository
{
    /**
     * Returns a plugin by its fully qualified class name.
     *
     * @param string $fqcn
     *
     * @return Plugin
     */
    public function findOneByBundleFQCN($fqcn)
    {
        $split = explode('\\', $fqcn);

        return $this->getEntityManager()
            ->createQuery('
                SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
                WHERE p.vendorName = :vendor
                AND p.bundleName = :bundle
            ')
            ->setParameter('vendor', $split[0])
            ->setParameter('bundle', $split[1])
            ->getOneOrNullResult();
    }

    /**
     * Returns a plugin by its fully qualified class name.
     *
     * @param string $name
     *
     * @return Plugin
     */
    public function findPluginByShortName($name)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
                WHERE CONCAT(p.vendorName, p.bundleName) LIKE :name
            ')
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }
}
