<?php

namespace Icap\WikiBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class SectionRepository extends NestedTreeRepository
{
    /**
     * @param Wiki $wiki
     * @param bool $isAdmin
     * @param User $user
     *
     * @return Tree $tree
     */
    public function buildSectionTree(Wiki $wiki, $isAdmin, User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('section')
            ->join('section.activeContribution', 'contribution')
            ->select('section, contribution, IDENTITY(section.parent) as parent')
            ->andWhere('section.root = :rootId')
            ->orderBy('section.root, section.left', 'ASC')
            ->setParameter('rootId', $wiki->getRoot()->getId());
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'section.deleted = :deleted',
                $queryBuilder->expr()->isNull('section.deleted')
            )
        )->setParameter('deleted', false);
        if ($isAdmin === false && $user !== null) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        'section.visible = :visible',
                        'section.author = :userId'
                    )
                )->setParameter('visible', true)->setParameter('userId', $user->getId());
        }
        if ($isAdmin === false && $user === null) {
            $queryBuilder
                ->andWhere('section.visible = :visible')
                ->setParameter('visible', true);
        }
        $options = ['decorate' => false];
        $tree = $this->buildTree($queryBuilder->getQuery()->getArrayResult(), $options);

        return $tree;
    }

    /**
     * @param Section $section
     */
    public function findSectionsForPosition(Section $section)
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
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt('section.left', $section->getLeft()),
                    $queryBuilder->expr()->lt('section.right', $section->getRight())
                    )
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'section.deleted = :deleted',
                    $queryBuilder->expr()->isNull('section.deleted')
                )
            )
            ->orderBy('section.root, section.left', 'ASC')
            ->setParameter('rootId', $section->getRoot())
            ->setParameter('deleted', false);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findDeletedSectionsQuery(Wiki $wiki)
    {
        $queryBuilder = $this->createQueryBuilder('section')
            ->join('section.activeContribution', 'contribution')
            ->select('section, contribution')
            ->andWhere('section.root = :rootId')
            ->andWhere('section.deleted = :deleted')
            ->orderBy('section.deletionDate', 'ASC')
            ->setParameter('deleted', true)
            ->setParameter('rootId', $wiki->getRoot()->getId());

        return $queryBuilder->getQuery();
    }

    public function deleteFromTree(Section $section)
    {
        //Update values for all descendants
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 'section.left-1')
            ->set('section.right', 'section.right-1')
            ->set('section.level', 'section.level-1')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.left', $section->getLeft()))
            ->andWhere($queryBuilder->expr()->lt('section.right', $section->getRight()))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update parentId of immediate descendants
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.parent', '?1')
            ->andWhere($queryBuilder->expr()->eq('section.parent', '?2'))
            ->setParameter(1, $section->getParent())
            ->setParameter(2, $section);
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update boundaries (left and right) for all nodes after deleted node
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', 'section.right-2')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.right', $section->getRight()))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 'section.left-2')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.left', $section->getRight()))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update deleted section
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 0)
            ->set('section.right', 0)
            ->set('section.level', -1)
            ->set('section.parent', '?1')
            ->set('section.deleted', '?2')
            ->set('section.deletionDate', '?3')
            ->andWhere('section.id = :sectionId')
            ->setParameter(1, null)
            ->setParameter(2, true)
            ->setParameter(3, new \DateTime('NOW'))
            ->setParameter('sectionId', $section->getId());
        $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function deleteSubtree(Section $section)
    {
        //Update deleted subtree
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 0)
            ->set('section.right', 0)
            ->set('section.level', -1)
            ->set('section.parent', '?1')
            ->set('section.deleted', '?2')
            ->set('section.deletionDate', '?3')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gte('section.left', $section->getLeft()))
            ->andWhere($queryBuilder->expr()->lte('section.right', $section->getRight()))
            ->setParameter(1, null)
            ->setParameter(2, true)
            ->setParameter(3, new \DateTime('NOW'))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        $boundaryWidth = $section->getRight() - $section->getLeft() + 1;
        //Update boundaries (left and right) for all nodes after deleted node
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', 'section.right-?1')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.right', $section->getRight()))
            ->setParameter(1, $boundaryWidth)
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 'section.left-?1')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.left', $section->getRight()))
            ->setParameter(1, $boundaryWidth)
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function restoreSection($section, $parent)
    {
        //Update restoring section data
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', $parent->getRight())
            ->set('section.right', $parent->getRight() + 1)
            ->set('section.level', $parent->getLevel() + 1)
            ->set('section.parent', '?1')
            ->set('section.deleted', '?2')
            ->andWhere('section.id = :sectionId')
            ->setParameter(1, $parent)
            ->setParameter(2, false)
            ->setParameter('sectionId', $section->getId());
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update parent (root) data
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', $parent->getRight() + 2)
            ->andWhere('section.id = :sectionId')
            ->setParameter('sectionId', $parent->getId());
        $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findDeletedSections(Wiki $wiki)
    {
        return $this->findDeletedSectionsQuery($wiki)->getArrayResult();
    }

    public function buildTree(array $nodes, array $options = [])
    {
        $nodeIds = [];
        $newNodes = [];
        foreach ($nodes as $item) {
            if (empty($item['parent']) || in_array($item['parent'], $nodeIds)) {
                $node = $item[0];
                $nodeIds[] = $node['id'];
                $newNodes[] = $node;
            }
        }

        return parent::buildTree($newNodes, $options);
    }
}
