<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\User\UserInterface;

use Claroline\CoreBundle\Repository\ResourceQueryBuilder;

class PathRepository extends EntityRepository
{
    public function findAccessibleByUser(array $roots = array (), array $userRoles)
    {
        $builder = new ResourceQueryBuilder();

        $builder->selectAsEntity(false, 'Innova\PathBundle\Entity\Path\Path');

        if (!empty($roots)) {
            $builder->whereRootIn($roots);
        }

        $builder->whereTypeIn(array ('innova_path'));
        $builder->whereRoleIn($userRoles);
        $builder->orderByName();

        $dql = $builder->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($builder->getParameters());

        $resources = $query->getResult();

        return $resources;
    }
}