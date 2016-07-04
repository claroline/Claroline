<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/24/16
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class PortalRepository.
 *
 * @DI\Service("claroline.repository.portal")
 */
class PortalRepository
{
    /** @var  EntityManager */
    private $em;

    private $conn;

    /**
     * PortalRepository constructor.
     *
     * @param EntityManager $em
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->conn = $em->getConnection();
    }

    public function findLastResourcesForTypes($resourceTypes, $limit = 5)
    {
        // if 'file' in resource types get images, videos and documents
        $files = array();
        if (($idx = array_search('file', $resourceTypes)) !== false) {
            unset($resourceTypes[$idx]);
            $files = $this->findLastFileResources($limit);
        }
        // if 'workspace' in resource types get last public workspaces
        $workspaces = array();
        if (($idx = array_search('workspace', $resourceTypes)) !== false) {
            unset($resourceTypes[$idx]);
            $workspaces = $this->findLastWorkspaces($limit);
        }

        $qb = $this
            ->createQueryBuilder('ClarolineCoreBundle:Resource\ResourceNode', 'rs')
            ->select($this->selectFieldsForQueryBuilder())
            ->leftJoin(
                'ClarolineCoreBundle:Resource\ResourceNode',
                'rs2',
                Join::WITH,
                'rs.resourceType = rs2.resourceType'
            )
            ->leftJoin('rs.resourceType', 'type')
            ->leftJoin('rs.creator', 'creator')
            ->andWhere('rs.modificationDate <= rs2.modificationDate')
            ->andWhere('rs.creationDate <= rs2.creationDate')
            ->andWhere('rs.id <= rs2.id')
            ->andWhere('type.name IN (:types)')
            ->andWhere('rs.publishedToPortal = :published')
            ->groupBy('rs.id')
            ->add('orderBy', 'rs.resourceType ASC, rs.modificationDate DESC, rs.creationDate DESC, rs.id DESC')
            ->andHaving('COUNT(rs) <= :limit')
            ->setParameter('types', $resourceTypes)
            ->setParameter('published', true)
            ->setParameter('limit', $limit);

        return array_merge($workspaces, $files, array('resources' => $qb->getQuery()->getResult()));
    }

    public function searchResourcesByResourceTypes(
        $query,
        $resourceTypes,
        $isTagEnabled = false,
        $page = 1,
        $maxPerPage = 50
    ) {
        $qb = $this
            ->createQueryBuilder('ClarolineCoreBundle:Resource\ResourceNode', 'rs')
            ->select($this->selectFieldsForQueryBuilder(true))
            ->leftJoin('rs.creator', 'creator')
            ->leftJoin('rs.resourceType', 'type')
            ->leftJoin('rs.icon', 'icon');
        if ($isTagEnabled) {
            $qb
                ->leftJoin('ClarolineTagBundle:TaggedObject', 'tObj', Join::WITH, 'tObj.objectId = rs.id')
                ->leftJoin('tObj.tag', 'tag', Join::WITH, 'tag.id=tObj.tag');
        }
        $this->handleResourceTypesToSearchQueryBuilder($qb, $resourceTypes);
        $this->addQueryTermsToSearchQueryBuilder($query, $qb, $isTagEnabled);
        $page = max(0, $page - 1);
        $qb->setMaxResults($maxPerPage);
        $qb->setFirstResult($page * $maxPerPage);

        return $qb->getQuery()->getResult();
    }

    public function countSearchResultsByResourceTypes($query, $resourceTypes, $isTagEnabled = false)
    {
        $qb = $this
            ->createQueryBuilder('ClarolineCoreBundle:Resource\ResourceNode', 'rs')
            ->select('COUNT(rs.id)')
            ->leftJoin('rs.resourceType', 'type');
        if ($isTagEnabled) {
            $qb
                ->leftJoin('ClarolineTagBundle:TaggedObject', 'tObj', Join::WITH, 'tObj.objectId = rs.id')
                ->leftJoin('tObj.tag', 'tag', Join::WITH, 'tag.id=tObj.tag');
        }
        $this->handleResourceTypesToSearchQueryBuilder($qb, $resourceTypes);
        $this->addQueryTermsToCountSearchResultsQueryBuilder($query, $qb, $isTagEnabled);

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    private function selectFieldsForQueryBuilder($selectIcon = false)
    {
        $selectStr = 'rs.id AS id, '.
            'rs.name AS name, '.
            'rs.creationDate AS creationDate, '.
            'creator.firstName AS creatorFirstName, '.
            'creator.lastName AS creatorLastName, '.
            'IDENTITY(rs.workspace) AS workspaceId, '.
            'type.name AS resourceType';
        if ($selectIcon) {
            $selectStr .= ', icon.relativeUrl AS relativeUrl';
        }

        return $selectStr;
    }

    private function findLastFileResources($limit = 5)
    {
        $images = $this->findLastFileResourcesByType('image', $limit);
        $videos = $this->findLastFileResourcesByType('video', $limit);
        $documents = $this->findLastFileResourcesByType('document', $limit);

        return array(
            'image' => $images,
            'video' => $videos,
            'document' => $documents,
        );
    }

    private function findLastFileResourcesByType($fileType, $limit = 5)
    {
        $qb = $this
            ->createQueryBuilder('ClarolineCoreBundle:Resource\ResourceNode', 'rs')
            ->leftJoin('rs.creator', 'creator')
            ->leftJoin('rs.resourceType', 'type')
            ->select($this->selectFieldsForQueryBuilder($fileType == 'image'))
            ->andWhere('rs.publishedToPortal = :published')
            ->add('orderBy', 'rs.modificationDate DESC, rs.creationDate DESC, rs.id DESC')
            ->setParameter('published', true)
            ->setMaxResults($limit);
        if ($fileType == 'document') {
            $qb
                ->andWhere('rs.mimeType NOT LIKE :imageType')
                ->andWhere('rs.mimeType NOT LIKE :videoType')
                ->andWhere('type.name = :fileType')
                ->setParameter('imageType', '%image%')
                ->setParameter('videoType', '%video%')
                ->setParameter('fileType', 'file');
        } else {
            $qb
                ->andWhere('rs.mimeType LIKE :fileType')
                ->setParameter('fileType', '%'.$fileType.'%');
            if ($fileType == 'image') {
                $qb->leftJoin('rs.icon', 'icon');
            }
        }

        return $qb->getQuery()->getResult();
    }

    private function findLastWorkspaces($limit = 5)
    {
        $qb = $this
            ->createQueryBuilder('ClarolineCoreBundle:Resource\ResourceNode', 'rs')
            ->select($this->selectFieldsForQueryBuilder())
            ->leftJoin('rs.creator', 'creator')
            ->leftJoin('ClarolineCoreBundle:Workspace\Workspace', 'ws', Join::WITH, 'ws.id = rs.workspace')
            ->leftJoin('rs.resourceType', 'type')
            ->andWhere('rs.parent IS NULL')
            ->andWhere('type.name = ?1')
            ->add('orderBy', 'rs.modificationDate DESC, rs.creationDate DESC, rs.id DESC')
            ->setParameter(1, 'directory')
            ->setMaxResults($limit);
        $qb
            ->andWhere($qb->expr()->orX('ws.displayable = ?2', 'ws.selfRegistration = ?2'))
            ->setParameter(2, true);

        return array('workspace' => $qb->getQuery()->getResult());
    }

    private function addQueryTermsToSearchQueryBuilder($query, QueryBuilder $qb, $isTagEnabled = false)
    {
        $terms = preg_split("/[\s,]+/", $query);
        $idx = count($qb->getParameters());
        $addWeight = '';
        foreach ($terms as $key => $term) {
            ++$idx;
            $addWeight .= (($key > 0) ? '+ ' : '').'IF(rs.name LIKE ?'.$idx.', 1, ';
            $addWeight .= $isTagEnabled ? 'IF(tag.name LIKE ?'.$idx.', 0.9, 0)) ' : '0) ';
            $qb->setParameter($idx, '%'.$term.'%');
        }
        $addWeight .= 'AS weight';
        $qb
            ->addSelect($addWeight)
            ->andHaving('weight > 0')
            ->add('orderBy', 'weight DESC, rs.modificationDate DESC, rs.creationDate DESC, rs.id DESC');
    }

    private function addQueryTermsToCountSearchResultsQueryBuilder($query, QueryBuilder $qb, $isTagEnabled = false)
    {
        $terms = preg_split("/[\s,]+/", $query);
        $idx = count($qb->getParameters());
        $orX = $qb->expr()->orX();
        foreach ($terms as $key => $term) {
            ++$idx;
            $orX->add('rs.name LIKE ?'.$idx);
            if ($isTagEnabled) {
                $orX->add('tag.name LIKE ?'.$idx);
            }
            $qb->setParameter($idx, '%'.$term.'%');
        }
        $qb->andWhere($orX);
    }

    private function handleResourceTypesToSearchQueryBuilder(QueryBuilder $qb, $resourceTypes)
    {
        $typesLen = count($resourceTypes);
        $orCondition = $qb->expr()->orX();
        $idx = count($qb->getParameters());
        if (($workspaceIdx = array_search('workspace', $resourceTypes)) !== false) {
            unset($resourceTypes[$workspaceIdx]);
            $orCondition->add($qb->expr()->andX(
                'type.name = ?'.++$idx,
                $qb->expr()->isNull('rs.parent'),
                $qb->expr()->orX('ws.displayable = ?'.++$idx, 'ws.selfRegistration = ?'.$idx)
            ));
            $qb
                ->leftJoin('ClarolineCoreBundle:Workspace\Workspace', 'ws', Join::WITH, 'ws.id = rs.workspace')
                ->setParameter($idx - 1, 'directory')
                ->setParameter($idx, true);
        }
        if ($typesLen == 1 && $workspaceIdx === false) {
            $type = array_pop($resourceTypes);
            if ($type == 'document') {
                $orCondition->add($qb->expr()->andX(
                    'type.name = ?'.++$idx,
                    'rs.mimeType NOT LIKE ?'.++$idx,
                    'rs.mimeType NOT LIKE ?'.++$idx
                ));
                $qb
                    ->setParameter($idx - 2, 'file')
                    ->setParameter($idx - 1, '%image%')
                    ->setParameter($idx, '%video%');
            } elseif ($type == 'image' || $type == 'video') {
                $orCondition->add($qb->expr()->andX(
                    'type.name = ?'.++$idx,
                    'rs.mimeType LIKE ?'.++$idx
                ));
                $qb
                    ->setParameter($idx - 1, 'file')
                    ->setParameter($idx, '%'.$type.'%');
            } else {
                $orCondition->add($qb->expr()->andX(
                    'type.name = ?'.++$idx
                ));
                $qb->setParameter($idx, $type);
            }
            $qb
                ->andWhere('rs.publishedToPortal = ?'.++$idx)
                ->setParameter($idx, true);
        } elseif ($typesLen > 0) {
            $orCondition->add($qb->expr()->andX(
                'type.name IN (?'.++$idx.')',
                'rs.publishedToPortal = ?'.++$idx
            ));
            $qb
                ->setParameter($idx - 1, $resourceTypes)
                ->setParameter($idx, true);
        }
        $qb->andWhere($orCondition);
    }

    private function createQueryBuilder($entityName, $alias, $indexBy = null)
    {
        return $this->em->createQueryBuilder()
            ->select($alias)
            ->from($entityName, $alias, $indexBy);
    }
}
