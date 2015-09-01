<?php
/**
 * Created by : VINCENT Eric
 * Date: 10/05/2015
*/

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class CommentRepository extends EntityRepository {

    /**
     *
     *  Fonctions créées pour InnovaCollecticielBundle.
     *  InnovaERV.
     *
    */

    /**
     *  Pour compter les commentaires non lus pour l'utilisateur indiqué
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

}
