<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/27/14
 * Time: 11:44 AM
 */

namespace Icap\WebsiteBundle\Repository;


use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\WebsiteBundle\Entity\Website;


class WebsitePageRepository extends NestedTreeRepository{
    /**
     * @param Website $website
     * @param boolean $isAdmin
     * @param boolean $isArray (return array or html code)
     * @param boolean $isMenu (get page collection or array collection)
     *
     * @return mixed
     */
    public function buildPageTree(Website $website, $isAdmin, $isArray = false, $isMenu = false)
    {
        $queryBuilder = $this->createQueryBuilder('page');
        if ($isMenu) {
            $queryBuilder->select('page');
        } else {
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
        }

        $queryBuilder->andWhere('page.website = :website')
            ->orderBy('page.root, page.left', 'ASC')
            ->setParameter('website', $website);

        if ($isAdmin===false) {
            $queryBuilder
                ->andWhere('page.visible = :visible')
                ->setParameter('visible', true);
        }

        if($isArray){
            $tree = $this->buildTreeArray($queryBuilder->getQuery()->getArrayResult());
        } else {
            $options = array('decorate' => false);
            $tree = $this->buildTree($queryBuilder->getQuery()->getArrayResult(), $options);
        }

        return $tree;
    }

    /**
     * @param Website $website
     * @param $pageId
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
        if ($isAdmin===false) {
            $queryBuilder
                ->andWhere('page.visible = :visible')
                ->setParameter('visible', true);
        }

        return $queryBuilder->getQuery()->getResult();
    }
} 