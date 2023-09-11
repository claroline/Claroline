<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Entity\UserProgression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FlashcardManager
{
    private ObjectManager $om;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
    }

    public function getShuffledCards(FlashcardDeck $flashcardDeck): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $cards = $flashcardDeck->getCards();
        $unseenCards = [];
        $failedCards = [];
        $passedCards = [];

        foreach ($cards as $card) {
            $userProgression = $this->om->getRepository(UserProgression::class)->findOneBy([
                'user' => $user,
                'flashcard' => $card,
            ]);

            if (!$userProgression) {
                $unseenCards[] = $card;
            } elseif (!$userProgression->isSuccessful()) {
                $failedCards[] = $card;
            } else {
                $passedCards[] = $card;
            }
        }

        shuffle($unseenCards);
        shuffle($failedCards);
        shuffle($passedCards);

        return array_merge($unseenCards, $failedCards, $passedCards);
    }
}
