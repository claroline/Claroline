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
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdministrationToolRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    public function __construct(RegistryInterface $registry, PluginManager $manager)
    {
        $this->bundles = $manager->getEnabled(true);

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
     * @param array $roles
     *
     * @return AdminTool[]
     */
    public function findByRoles(array $roles)
    {
        $rolesNames = [];
        $isAdmin = false;

        foreach ($roles as $role) {
            $rolesNames[] = $role->getRole();

            if ('ROLE_ADMIN' === $role->getRole()) {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            return $this->findAll();
        } else {
            $dql = '
                SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
                JOIN tool.roles role
                LEFT JOIN tool.plugin p
                WHERE role.name IN (:roleNames)
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR tool.plugin IS NULL
                )
            ';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        if (!$isAdmin) {
            $query->setParameter('roleNames', $rolesNames);
        }

        return $query->getResult();
    }
}
