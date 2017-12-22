<?php

namespace Innova\PathBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\ResourceQueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Innova\PathBundle\Entity\PathWidgetConfig;

class PathRepository extends EntityRepository
{
    public function findWidgetPaths(array $userRoles, array $roots = [], PathWidgetConfig $config = null)
    {
        $builder = new ResourceQueryBuilder();

        $builder->selectAsEntity(false, 'Innova\PathBundle\Entity\Path\Path');

        if (!empty($roots)) {
            $builder->whereRootIn($roots);
        }

        $builder->whereTypeIn(['innova_path']);

        if (!in_array('ROLE_ADMIN', $userRoles)) {
            $builder->whereRoleIn($userRoles);
        }

        // Add filters if defined
        if ($config) {
            // Add widget STATUS filters
            $statusList = $config->getStatus();
            if (!empty($statusList)) {
                $whereStatus = [];
                foreach ($statusList as $status) {
                    switch ($status) {
                        case 'draft':
                            $whereStatus[] = 'node.published = 0';
                            break;

                        case 'published':
                            $whereStatus[] = '(node.published = 1 AND resource.modified = 0)';
                            break;

                        case 'modified':
                            $whereStatus[] = '(node.published = 1 AND resource.modified = 1)';
                            break;
                    }
                }

                if (!empty($whereStatus)) {
                    $builder->addWhereClause('('.implode($whereStatus, ' OR ').')');
                }
            }

            // Add widget TAG filters
            $tagList = $config->getTags();
            if (0 < count($tagList)) {
                $tags = [];
                foreach ($tagList as $tag) {
                    $tags[] = $tag->getId();
                }

                // Join with the corresponding TaggedObject entities
                $builder->addJoinClause('LEFT JOIN ClarolineTagBundle:TaggedObject AS t WITH t.objectId = node.id');
                $builder->addWhereClause('t.id IS NOT NULL');
                $builder->addWhereClause('t.tag IN ('.implode($tags, ', ').')');
            }
        }

        $builder->orderByName();

        $dql = $builder->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($builder->getParameters());

        $resources = $query->getResult();

        return $resources;
    }

    /**
     * Get all Paths of the Platform.
     *
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     *
     * @return array
     */
    public function findPlatformPaths($toPublish = false)
    {
        $builder = $this->createQueryBuilder('p');

        $builder->join('p.resourceNode', 'r');

        // Get only Paths which need publishing
        if ($toPublish) {
            $this->whereToPublish($builder);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * Get all Paths of a Workspace.
     *
     * @param Workspace $workspace
     * @param bool      $toPublish If false, returns all paths, if true returns only paths which need publishing
     *
     * @return array
     */
    public function findWorkspacePaths(Workspace $workspace, $toPublish = false)
    {
        $builder = $this->createQueryBuilder('p');

        // Join with resourceNode
        $builder->join('p.resourceNode', 'r', 'WITH', 'r.workspace = '.$workspace->getId());

        // Get only Paths which need publishing
        if ($toPublish) {
            $this->whereToPublish($builder);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * Get all published Paths.
     *
     * @param bool $withPending If true, returns all published paths, including the ones with pending changes
     *
     * @return array
     */
    public function findPublishedPath($withPending = false)
    {
        $builder = $this->createQueryBuilder('p');

        $builder->join('p.resourceNode', 'r');

        $builder->where('r.published = :published');
        $builder->setParameter('published', true);

        if (!$withPending) {
            $builder->andWhere('p.modified = :modified');
            $builder->setParameter('modified', $withPending);
        }

        return $builder->getQuery()->getResult();
    }

    private function whereToPublish(QueryBuilder $builder)
    {
        $builder->where('r.published = :published');
        $builder->setParameter('published', false);

        $builder->orWhere('p.modified = :modified');
        $builder->setParameter('modified', true);

        return $this;
    }
}
