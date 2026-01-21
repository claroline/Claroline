<?php

namespace Icap\LessonBundle\Repository;

use Doctrine\ORM\NoResultException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\LessonBundle\Entity\Chapter;

class ChapterRepository extends NestedTreeRepository
{
    public function getChapterTree(Chapter $chapter, bool $includeChapter = true)
    {
        return $this->childrenHierarchy($chapter, false, [], $includeChapter);
    }

    public function getNextChapter(Chapter $chapter): ?Chapter
    {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();

            return $this->getEntityManager()->createQueryBuilder()->add('select', 'c')
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
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function getPreviousChapter(Chapter $chapter): ?Chapter
    {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();

            return $this->getEntityManager()->createQueryBuilder()->add('select', 'c')
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
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function countWithParent(int $lessonId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.lesson = :lesson')
            ->andWhere('c.parent IS NOT NULL')
            ->setParameter('lesson', $lessonId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
