<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResourceTypeRepository extends ServiceEntityRepository
{
    private array $bundles = [];

    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, ResourceType::class);
    }

    /**
     * Returns all the resource types introduced by plugins.
     *
     * @param bool $filterEnabled - when true, it will only return resource types for enabled plugins
     *
     * @return ResourceType[]
     */
    public function findAll(bool $filterEnabled = true): array
    {
        if (!$filterEnabled) {
            return parent::findAll();
        }

        $dql = '
          SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
          LEFT JOIN rt.plugin p
          WHERE (CONCAT(p.vendorName, p.bundleName) IN (:bundles)
          OR rt.plugin is NULL)
          AND rt.isEnabled = true';

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * Returns enabled resource types by their names.
     *
     * @return ResourceType[]
     */
    public function findByNames(array $names, bool $enabled = true): array
    {
        if (count($names) > 0) {
            $dql = '
                SELECT r
                FROM Claroline\CoreBundle\Entity\Resource\ResourceType r
                WHERE r.name IN (:names) 
            ';

            if ($enabled) {
                $dql .= ' AND r.isEnabled = true';
            }

            $query = $this->getEntityManager()->createQuery($dql);
            $query->setParameter('names', $names);
            $result = $query->getResult();
        } else {
            $result = [];
        }

        return $result;
    }
}
