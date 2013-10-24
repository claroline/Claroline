<?php

namespace Icap\WikiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class SectionRepository extends NestedTreeRepository
{    
    /**
     * @param Wiki $wiki
     * @param boolean $isAdmin
     * @return Tree $tree
     */
    public function buildSectionTree(Wiki $wiki, $isAdmin)
    {
        $queryBuilder = $this->createQueryBuilder('section')
            ->join('section.activeContribution', 'contribution')
            ->select('section, contribution')
            ->andWhere('section.root = :rootId')
            ->orderBy('section.root, section.left', 'ASC')
            ->setParameter('rootId', $wiki->getRoot()->getId());
        if ($isAdmin===false) {
            $queryBuilder
                ->andWhere('section.visible = :visible')
                ->setParameter('visible', true);
        }
        $options = array('decorate' => false);
        $tree = $this->buildTree($queryBuilder->getQuery()->getArrayResult(), $options);
        
        return $tree;
    }

    /**
     * @param Section $section 
     */
    public function findSectionsForPosition (Section $section)
    {
        $queryBuilder = $this->createQueryBuilder('section');
        $queryBuilder
            ->join('section.activeContribution', 'contribution')
            ->join('section.parent', 'parent')
            ->select('section.id, contribution.title, parent.id as parentId')
            ->andWhere('section.root = :rootId')
            ->andWhere(
                $queryBuilder->expr()->gt('section.level', 0)
            )
            ->andWhere($queryBuilder->expr()->not(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->gt('section.left', $section->getLeft()), 
                    $queryBuilder->expr()->lt('section.right', $section->getRight())
                    )
                )
            )
            ->orderBy('section.root, section.left', 'ASC')
            ->setParameter('rootId', $section->getRoot());

        return $queryBuilder->getQuery()->getArrayResult();
    }
}