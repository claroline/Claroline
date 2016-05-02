<?php
/**
 * Created by : VINCENT Eric
 * Date: 10/05/2015.
*/

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use Innova\CollecticielBundle\Entity\Document;

class CommentRepository extends EntityRepository
{
    /**
     *  Fonctions créées pour InnovaCollecticielBundle.
     *  InnovaERV.
     */

    /**
     * Pour compter les commentaires non lus pour l'utilisateur indiqué.
     *
     * @param $user
     */
    public function countCommentNotRead(User $user)
    {
        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->leftJoin('comment.comments', 'comment_read')
            ->andWhere('comment_read.id is null')
            ->andWhere('comment.user = :user')
            ->setParameter('user', $user);

        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->andWhere('comment.user = :user')
            ->setParameter('user', $user);

        return count($qb->getQuery()->getResult());
    }

    /**
     * Ajout pour savoir si le document a un commentaire lu par l'enseignant.
     *
     * @param $userId
     * @param $docId
     */
    public function commentReadForATeacherOrNot(User $user, $documentId)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->leftJoin('comment.comments', 'comment_read')
            ->andWhere('comment.document = :document')
            ->andWhere('comment.user != :user')
            ->andWhere('comment_read.user = :user')
            ->setParameter('document', $documentId)
            ->setParameter('user', $user);

        $numberCommentRead = count($qb->getQuery()->getResult());

        return $numberCommentRead;
    }

    /**
     * Ajout pour savoir si le document a un commentaire lu par l'enseignant.
     *
     * @param $userId
     * @param $docId
     */
    public function commentReadForATeacherOrNot2(User $user, $documentId)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->andWhere('comment.document = :document')
            ->andWhere('comment.user = :user')
            ->setParameter('document', $documentId)
            ->setParameter('user', $user);

        $numberCommentRead = count($qb->getQuery()->getResult());

        return $numberCommentRead;
    }

    /**
     * Ajout pour savoir si le document a un commentaire lu par l'enseignant.
     *
     * @param $userId
     * @param $docId
     */
    public function commentReadForATeacherOrNot3(User $user, $documentId)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->leftJoin('comment.comments', 'comment_read')
            ->andWhere('comment.document = :document')
            ->andWhere('comment.user = :user')
            ->andWhere('comment_read.user = :user')
            ->setParameter('document', $documentId)
            ->setParameter('user', $user);

        $numberCommentRead = count($qb->getQuery()->getResult());

        return $numberCommentRead;
    }

    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué.
     *
     * @param $documentId
     */
    public function teacherCommentDocArray(Document $document)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('comment')
            ->select('comment')
            ->andWhere('comment.document = :document')
            ->setParameter('document', $document)
            ->addOrderBy('comment.commentDate', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }
}
