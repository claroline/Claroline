<?php

namespace Icap\WikiBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class SectionRepository extends NestedTreeRepository
{
    /**
     * @param User $user
     * @param bool $isAdmin
     *
     * @return array|string $tree
     */
    public function buildSectionTree(Wiki $wiki, User $user = null, $isAdmin = false)
    {
        $queryBuilder = $this->createQueryBuilder('section')
            ->join('section.activeContribution', 'contribution')
            ->leftJoin('section.author', 'author')
            ->select('section, contribution, author, IDENTITY(section.parent) as parent')
            ->andWhere('section.root = :rootId')
            ->orderBy('section.root, section.left', 'ASC')
            ->setParameter('rootId', $wiki->getRoot()->getId());
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'section.deleted = :deleted',
                $queryBuilder->expr()->isNull('section.deleted')
            )
        )->setParameter('deleted', false);
        if (false === $isAdmin && null !== $user) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        'section.visible = :visible',
                        'section.author = :userId'
                    )
                )->setParameter('visible', true)->setParameter('userId', $user->getId());
        }
        if (false === $isAdmin && null === $user) {
            $queryBuilder
                ->andWhere('section.visible = :visible')
                ->setParameter('visible', true);
        }
        $options = ['decorate' => false];
        $tree = $this->buildTree($queryBuilder->getQuery()->getArrayResult(), $options);

        return $tree;
    }

    /**
     * @return array
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

    public function deleteFromTree(Section $section)
    {
        //Update values for all descendants
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.parent', '?1')
            ->andWhere($queryBuilder->expr()->eq('section.parent', '?2'))
            ->setParameter(1, $section->getParent())
            ->setParameter(2, $section);
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update boundaries (left and right) for all nodes after deleted node
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', 'section.right-2')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.right', $section->getRight()))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 'section.left-2')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.left', $section->getRight()))
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        //Update deleted section
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', 'section.right-?1')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.right', $section->getRight()))
            ->setParameter(1, $boundaryWidth)
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.left', 'section.left-?1')
            ->andWhere('section.root = :root')
            ->andWhere($queryBuilder->expr()->gt('section.left', $section->getRight()))
            ->setParameter(1, $boundaryWidth)
            ->setParameter('root', $section->getRoot());
        $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function restoreSection(Section $section, $parent = null)
    {
        //Update restoring section data
        if (null === $parent) {
            $parent = $section->getWiki()->getRoot();
        }
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update('Icap\WikiBundle\Entity\Section', 'section')
            ->set('section.right', $parent->getRight() + 2)
            ->andWhere('section.id = :sectionId')
            ->setParameter('sectionId', $parent->getId());
        $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findSectionsBy($criteria = [])
    {
        $qb = $this->createQueryBuilder('section');
        foreach ($criteria as $name => $value) {
            if (is_array($value)) {
                $qb
                    ->andWhere('section.'.$name.' IN (:'.$name.')');
            } else {
                $qb->andWhere('section.'.$name.' = :'.$name);
            }
            $qb->setParameter($name, $value);
        }

        return $qb->getQuery()->getResult();
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
