<?php

namespace Claroline\FlashcardBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\UserProgression;
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

    public function findOneByUserAndFlashcard(User $user, Flashcard $flashcard): ?UserProgression
    {
        return $this->findOneBy([
            'user' => $user,
            'flashcard' => $flashcard,
        ]);
    }
}
