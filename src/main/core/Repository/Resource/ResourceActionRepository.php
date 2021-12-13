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

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ResourceActionRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, MenuAction::class);
    }

    /**
     * Returns all the resource actions introduced by plugins.
     *
     * @param bool $filterEnabled - when true, it will only return resource types for enabled plugins
     *
     * @return MenuAction[]
     */
    public function findAll($filterEnabled = true)
    {
        if (!$filterEnabled) {
            return parent::findAll();
        }

        return $this->_em
            ->createQuery('
                SELECT m FROM Claroline\CoreBundle\Entity\Resource\MenuAction m
                LEFT JOIN m.plugin p
                WHERE (CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR m.plugin is NULL)
            ')
            ->setParameter('bundles', $this->bundles)
            ->getResult();
    }
}
