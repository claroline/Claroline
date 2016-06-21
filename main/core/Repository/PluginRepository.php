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
        $dql = '
            SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
            WHERE p.vendorName = :vendor
            AND p.bundleName = :bundle
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('vendor', $split[0]);
        $query->setParameter('bundle', $split[1]);

        return $query->getOneOrNullResult();
    }

    /**
     * Returns a plugin by its fully qualified class name.
     *
     * @param string $fqcn
     *
     * @return Plugin
     */
    public function findPluginByShortName($name)
    {
        $dql = '
            SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
            WHERE CONCAT(p.vendorName, p.bundleName) LIKE :name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('name', $name);

        return $query->getOneOrNullResult();
    }
}
