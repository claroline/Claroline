<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Tool;

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Manager\PluginManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdministrationToolRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, AdminTool::class);
    }

    public function findAll()
    {
        $dql = 'SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
            LEFT JOIN tool.plugin p
            WHERE CONCAT(p.vendorName, p.bundleName) IN (:bundles)
            OR tool.plugin is NULL';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    /**
     * @return AdminTool[]
     */
    public function findByRoles(array $rolesNames)
    {
        $isAdmin = in_array('ROLE_ADMIN', $rolesNames);

        $dql = '
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
            LEFT JOIN tool.roles role
            LEFT JOIN tool.plugin p
            WHERE (
                CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                OR tool.plugin IS NULL
            )
        ';

        if (!$isAdmin) {
            $dql .= ' AND role.name IN (:roleNames)';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        if (!$isAdmin) {
            $query->setParameter('roleNames', $rolesNames);
        }

        return $query->getResult();
    }
}
