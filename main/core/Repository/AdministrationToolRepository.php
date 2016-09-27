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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdministrationToolRepository extends EntityRepository implements ContainerAwareInterface
{
    private $bundles = [];
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
    }

    public function findAll()
    {
        $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
            LEFT JOIN tool.plugin p
            WHERE CONCAT(p.vendorName, p.bundleName) IN (:bundles)
            OR tool.plugin is NULL";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        return $query->getResult();
    }

    public function findByRoles(array $roles)
    {
        $rolesNames = [];
        $isAdmin = false;

        foreach ($roles as $role) {
            $rolesNames[] = $role->getRole();

            if ($role->getRole() === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            return $this->findAll();
        } else {
            $dql = "
                SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
                JOIN tool.roles role
                LEFT JOIN tool.plugin p
                WHERE role.name IN (:roleNames)
                AND (
                    CONCAT(p.vendorName, p.bundleName) IN (:bundles)
                    OR tool.plugin IS NULL
                )
            ";
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('bundles', $this->bundles);

        if (!$isAdmin) {
            $query->setParameter('roleNames', $rolesNames);
        }

        return $query->getResult();
    }
}
