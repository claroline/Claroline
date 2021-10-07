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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;

class ResourceNodeRepository extends MaterializedPathRepository
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('n')
            ->join('n.workspace', 'w')
            ->where('UPPER(n.name) LIKE :search')
            ->andWhere('w.archived = false')
            ->andWhere('n.active = true')
            ->andWhere('n.published = true')
            ->andWhere('n.hidden = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUuidOrSlug($id)
    {
        return $this->createQueryBuilder('n')
            ->where('n.uuid = :id OR n.slug = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
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
     * @return ResourceNode
     */
    public function findWorkspaceRoot(Workspace $workspace)
    {
        $results = $this->createQueryBuilder('n')
            ->where('n.parent IS NULL')
            ->andWhere('n.workspace = :workspace')
            ->setParameter('workspace', $workspace->getId())
            ->getQuery()
            ->getResult();

        // in case something was messed up at some point
        if (1 === count($results)) {
            return $results[0];
        }

        // we find the one with the most children as a restoration trick
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
    public function findDescendants(ResourceNode $resource)
    {
        return $this->createQueryBuilder('n')
            ->where('n.path LIKE :path_like')
            ->andWhere('n.path != :path') // do not include current resource
            ->setParameter('path_like', $resource->getPath().'%')
            ->setParameter('path', $resource->getPath())
            ->getQuery()
            ->getResult();
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
            WHERE node.parent = :node';

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
                ->join('w.organizations', 'o')
                ->andWhere('o IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
