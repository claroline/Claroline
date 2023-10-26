<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Serializer\FlashcardDeckSerializer;

class FlashcardManager
{
    private ObjectManager $om;
    private FlashcardDeckSerializer $flashcardDeckSerializer;

    public function __construct(ObjectManager $om, FlashcardDeckSerializer $flashcardDeckSerializer)
    {
        $this->om = $om;
        $this->flashcardDeckSerializer = $flashcardDeckSerializer;
    }

    public function getCardDraw(FlashcardDeck $flashcardDeck, ResourceEvaluation $currentAttempt): array
    {
        $cards = self::shuffleCardByAttempt($flashcardDeck->getCards(), $currentAttempt);
        $unseenCards = [];
        $seenCards = [];

        foreach ($cards as $card) {
            $cardDrawnProgression = $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'resourceEvaluation' => $currentAttempt,
                'flashcard' => $card
            ]);

            if ($cardDrawnProgression) {
                $seenCards[] = $this->flashcardDeckSerializer->serializeCard($card);
            } else {
                $unseenCards[] = $this->flashcardDeckSerializer->serializeCard($card);
            }
        }

        return [
            'unseen' => $unseenCards,
            'seen' => $seenCards,
        ];
    }

    private static function shuffleCardByAttempt($cards, ?ResourceEvaluation $attempt)
    {
        // Mélange les cartes de la même façon en fonction de l'ID de l'essai en cours
        // Algo : https://en.wikipedia.org/wiki/Fisher–Yates_shuffle

        mt_srand($attempt ? $attempt->getId() : 0);
        for ($i = count($cards) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $cards[$i];
            $cards[$i] = $cards[$j];
            $cards[$j] = $tmp;
        }

        return $cards;
    }
}
