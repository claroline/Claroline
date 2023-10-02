<?php

namespace Claroline\FlashcardBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserProgressionRepository extends EntityRepository
{
    public function countCardsSeenByUserForDeck(User $user, $deckCards)
    {
        return $this->createQueryBuilder('up')
            ->select('count(up)')
            ->where('up.user = :user')
            ->andWhere('up.flashcard IN (:cards)')
            ->setParameter('user', $user->getId())
            ->setParameter('cards', $deckCards)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
