<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

use Claroline\CoreBundle\Repository\ResourceQueryBuilder;
use Doctrine\ORM\QueryBuilder;

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

    /**
     * Get all Paths of the Platform
     * @param  bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     * @return array
     */
    public function findPlatformPaths($toPublish = false)
    {
        $builder = $this->createQueryBuilder('p');

        // Get only Paths which need publishing
        if ($toPublish) {
            $this->whereToPublish($builder);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * Get all Paths of a Workspace
     * @param Workspace $workspace
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     * @return array
     */
    public function findWorkspacePaths(Workspace $workspace, $toPublish = false)
    {
        $builder = $this->createQueryBuilder('p');

        // Join with resourceNode
        $builder->join('p.resourceNode', 'r', 'WITH', 'r.workspace = ' . $workspace->getId());

        // Get only Paths which need publishing
        if ($toPublish) {
            $this->whereToPublish($builder);
        }

        return $builder->getQuery()->getResult();
    }

    private function whereToPublish(QueryBuilder $builder)
    {
        $builder->where('p.published = :published');
        $builder->setParameter('published', false);

        $builder->orWhere('p.modified = :modified');
        $builder->setParameter('modified', true);

        return $this;
    }
}