<?php

namespace Claroline\PluginBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LauncherRepository extends EntityRepository
{
    public function findByAccessRoles(array $roleNames)
    {
        foreach ($roleNames as $key => $roleName)
        {
            $roleNames[$key] = "'{$roleName}'";
        }

        $roleValues = implode(', ', $roleNames);

        $dql = "SELECT l FROM Claroline\PluginBundle\Entity\ApplicationLauncher l " .
               "JOIN l.accessRoles r WHERE r.name IN ({$roleValues})";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}