<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/27/14
 * Time: 11:44 AM.
 */

namespace Icap\WebsiteBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\WebsiteBundle\Entity\Website;

class WebsitePageRepository extends NestedTreeRepository
{
    /**
     * @param Website $website
     * @param bool    $isAdmin
     * @param bool    $isMenu  (get page collection or array collection)
     *
     * @return mixed
     */
    public function buildPageTree(Website $website, $isAdmin, $isMenu = false)
    {
        $queryBuilder = $this->createQueryBuilder('page');
        if (!$isMenu) {
            $queryBuilder->leftJoin('page.resourceNode', 'resource')
                ->leftJoin('resource.workspace', 'resourceWorkspace')
                ->select('
                page.id,
                page.title,
                page.visible,
                page.isSection,
                page.isHomepage,
                page.description,
                page.left,
                page.level,
                page.right,
                IDENTITY(page.parent) AS parent,
                resource.id AS resourceNode,
                resource.name AS resourceNodeName,
                resourceWorkspace.name AS resourceNodeWorkspace,
                page.resourceNodeType,
                page.url,
                page.target,
                page.richText,
                page.root,
                page.type
            ');
        } else {
            $queryBuilder->select('
                page.id,
                page.visible,
                page.title,
                page.isSection,
                page.url,
                page.target,
                page.left,
                page.level,
                page.right,
                page.root,
                page.type
            ');
        }

        $queryBuilder->andWhere('page.website = :website')
            ->orderBy('page.root, page.left', 'ASC')
            ->setParameter('website', $website);

        if ($isAdmin === false) {
            $queryBuilder
                ->andWhere('page.visible = :visible')
                ->setParameter('visible', true);
        }

        $options = ['decorate' => false];
        $nodes = $queryBuilder->getQuery()->getArrayResult();
        $tree = $this->buildTreeArray($nodes, $options);

        return $tree;
    }

    /**
     * @param Website $website
     * @param $pageIds
     * @param $isAdmin
     * @param $isAPI
     *
     * @return mixed
     */
    public function findPages(Website $website, $pageIds, $isAdmin, $isAPI = false)
    {
        $queryBuilder = $this->createQueryBuilder('page');
        if ($isAPI) {
            $queryBuilder->select('
                page.id,
                page.visible,
                page.title,
                page.isSection,
                page.left,
                page.level,
                page.right,
                page.root,
                IDENTITY(page.parent)
                ');
        } else {
            $queryBuilder->select('page');
        }
        $queryBuilder->andWhere('page.website = :website')
            ->andWhere($queryBuilder->expr()->in('page.id', $pageIds))
            ->orderBy('page.root, page.left', 'ASC')
            ->setParameter('website', $website);
        if ($isAdmin === false) {
            $queryBuilder
                ->andWhere('page.visible = :visible')
                ->setParameter('visible', true);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param ResourceNode $resourceNode
     *
     * @return WebsitePage
     */
    public function findRootPageByResourceNode(ResourceNode $resourceNode)
    {
        return $this->createQueryBuilder('page')
            ->leftJoin('page.website', 'website')
            ->leftJoin('website.resourceNode', 'resource')
            ->where('resource = :resourceNode')
            ->andWhere('page.type = :type')
            ->setParameter('resourceNode', $resourceNode)
            ->setParameter('type', 'root')
            ->getQuery()
            ->getSingleResult();
    }
}
