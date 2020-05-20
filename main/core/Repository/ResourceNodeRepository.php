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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Repository for AbstractResource entities. The methods of this class may return
 * entities either as objects or as as arrays (see their respective documentation).
 */
class ResourceNodeRepository extends MaterializedPathRepository implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var ResourceQueryBuilder */
    private $builder;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
        $this->builder = new ResourceQueryBuilder();
        $this->builder->setBundles($bundles);
    }

    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('n')
            ->where('UPPER(n.name) LIKE :search')
            ->andWhere('n.active = true')
            ->andWhere('n.published = true')
            ->andWhere('n.hidden = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string|int $id          The id or guid of the node
     * @param null       $lockMode
     * @param null       $lockVersion
     *
     * @return ResourceNode|null
     *
     * @deprecated there are other methods to do it (see ObjectManager). Do not override base find().
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $qb = $this->createQueryBuilder('n');

        if (preg_match('/^\d+$/', $id)) {
            $qb->where('n.id = :id');
        } else {
            $qb->where('n.uuid = :id');
        }

        return $qb
            ->getQuery()
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }

    /**
     * Returns the root directory of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return ResourceNode
     */
    public function findWorkspaceRoot(Workspace $workspace)
    {
        $this->builder->selectAsEntity()
            ->whereInWorkspace($workspace)
            ->whereParentIsNull();
        $query = $this->_em->createQuery($this->builder->getDql());
        $query->setParameters($this->builder->getParameters());

        /** @var ResourceNode[] $results */
        $results = $query->getResult();

        //in case something was messed up at some point
        if (1 === count($results)) {
            return $results[0];
        }

        //we find the one with the most children as a restoration trick
        $maxChildren = 0;
        $toReturn = 1 < count($results) ? $results[0] : null;

        foreach ($results as $result) {
            $count = count($result->getChildren());
            if ($count > $maxChildren) {
                $maxChildren = $count;
                $toReturn = $result;
            }
        }

        return $toReturn;
    }

    /**
     * Returns the descendants of a resource.
     *
     * @param ResourceNode $resource The resource node to start with
     *
     * @return ResourceNode[]
     */
    public function findDescendants(
        ResourceNode $resource
    ) {
        $this->builder->selectAsEntity(true)
            ->wherePathLike($resource->getPath(), false);

        $query = $this->_em->createQuery($this->builder->getDql());
        $query->setParameters($this->builder->getParameters());

        return $query->getResult();
    }

    /**
     * Returns an array of different file types with the number of resources that
     * belong to this type.
     *
     * @param int            $max
     * @param Organization[] $organizations
     *
     * @return array
     */
    public function findMimeTypesWithMostResources($max, array $organizations = [])
    {
        $qb = $this->createQueryBuilder('resource')
            ->select('resource.mimeType AS type, COUNT(resource.id) AS total')
            ->where('resource.mimeType IS NOT NULL')
            ->groupBy('resource.mimeType')
            ->orderBy('total', 'DESC');

        if (!empty($organizations)) {
            $qb
                ->leftJoin('resource.workspace', 'ws')
                ->leftJoin('ws.organizations', 'o')
                ->andWhere('o IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        if ($max > 1) {
            $qb->setMaxResults($max);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLastIndex(ResourceNode $node)
    {
        $dql = '
            SELECT MAX(node.index)
            FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node
            where node.parent = :node';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('node', $node->getId());

        return $query->getSingleScalarResult();
    }

    public function countActiveResources(array $workspaces = [], array $organizations = []): int
    {
        $qb = $this->createQueryBuilder('node')
            ->select('COUNT(node)')
            ->where('node.active = true');

        if (!empty($workspaces)) {
            $qb
                ->andWhere('node.workspace IN (:workspaces)')
                ->setParameter('workspaces', $workspaces);
        }

        if (!empty($organizations)) {
            $qb
                ->join('node.workspace', 'w')
                ->andWhere('w.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
