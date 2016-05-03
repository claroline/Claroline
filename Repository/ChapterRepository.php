<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nicolas
 * Date: 18/10/13
 * Time: 09:36
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class ChapterRepository extends NestedTreeRepository
{
    public function getFirstChapter(Lesson $lesson)
    {
        return $this->findOneBy(array('lesson' => $lesson, 'root' => $lesson->getRoot()->getId(), 'left' => 2));
    }

    public function getChapterTree(Chapter $chapter)
    {
        return $this->childrenHierarchy($chapter, false, array(), true);
    }

    /**
     * @param Chapter $chapter
     *
     * @return Tree $tree
     */
    public function buildChapterTree(Chapter $chapter, $fields = 'chapter')
    {
        $queryBuilder = $this->createQueryBuilder('chapter')
            ->select($fields)
            ->andWhere('chapter.root = :rootId')
            ->orderBy('chapter.root, chapter.left', 'ASC')
            ->setParameter('rootId', $chapter->getId());
        $options = array('decorate' => false);
        $tree = $this->buildTree($queryBuilder->getQuery()->getArrayResult(), $options);

        return $tree;
    }

    public function getChapterAndChapterChildren(Chapter $chapter)
    {
        return $this->children($chapter, false, null, 'ASC', true);
    }

    public function getChapterChildren(Chapter $chapter)
    {
        return $this->children($chapter, false, null, 'ASC', false);
    }

    public function getChapterFirstChild(Chapter $chapter)
    {
        try {
            return $this->childrenQueryBuilder($chapter)
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getChapterAndDirectChapterChildren(Chapter $chapter)
    {
        return $this->children($chapter, true, null, 'ASC', true);
    }

    public function getDirectChapterChildren(Chapter $chapter)
    {
        return $this->children($chapter, true, null, 'ASC', false);
    }

    public function getNextSibling(Chapter $chapter)
    {
        try {
            return $this->getNextSiblingsQueryBuilder($chapter)
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getPreviousSibling(Chapter $chapter)
    {
        try {
            return $this->getPrevSiblingsQueryBuilder($chapter)
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getNextChapter(Chapter $chapter)
    {
        try {
            $qb = $this->_em->createQueryBuilder();

            return $this->_em->createQueryBuilder()->add('select', 'c')
                ->add('from', 'Icap\LessonBundle\Entity\Chapter c')
                ->innerJoin('c.lesson', ' l')
                ->where($qb->expr()->andx(
                    $qb->expr()->gt('c.left', '?1'),
                    $qb->expr()->eq('l.id', '?2')
                ))
                ->orderBy('c.left', 'ASC')
                ->setParameter(1, $chapter->getLeft())
                ->setParameter(2, $chapter->getLesson())
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getPreviousChapter($chapter)
    {
        try {
            $qb = $this->_em->createQueryBuilder();

            return $this->_em->createQueryBuilder()->add('select', 'c')
                ->add('from', 'Icap\LessonBundle\Entity\Chapter c')
                ->innerJoin('c.lesson', ' l')
                ->where($qb->expr()->andx(
                    $qb->expr()->lt('c.left', '?1'),
                    $qb->expr()->eq('l.id', '?2'),
                    $qb->expr()->not($qb->expr()->eq('c.id', '?3'))
                ))
                ->orderBy('c.left', 'DESC')
                ->setParameter(1, $chapter->getLeft())
                ->setParameter(2, $chapter->getLesson()->getId())
                ->setParameter(3, $chapter->getLesson()->getRoot()->getId())
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getChapterById($chapterId, $lessonId)
    {
        try {
            $qb = $this->_em->createQueryBuilder();

            return $this->_em->createQueryBuilder()->add('select', 'c')
                ->add('from', 'Icap\LessonBundle\Entity\Chapter c')
                ->innerJoin('c.lesson', ' l')
                ->where($qb->expr()->andx(
                    $qb->expr()->eq('c.id', '?1'),
                    $qb->expr()->eq('l.id', '?2')
                ))
                ->setParameter(1, $chapterId)
                ->setParameter(2, $lessonId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }

    public function getChapterBySLug($chapterSlug, $lessonId)
    {
        try {
            $qb = $this->_em->createQueryBuilder();

            return $this->_em->createQueryBuilder()->add('select', 'c')
                ->add('from', 'Icap\LessonBundle\Entity\Chapter c')
                ->innerJoin('c.lesson', ' l')
                ->where($qb->expr()->andx(
                    $qb->expr()->eq('c.slug', '?1'),
                    $qb->expr()->eq('l.id', '?2')
                ))
                ->setParameter(1, $chapterSlug)
                ->setParameter(2, $lessonId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return;
        }
    }
}
