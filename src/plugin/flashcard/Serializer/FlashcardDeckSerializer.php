<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Entity\Flashcard;

class FlashcardDeckSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return FlashcardDeck::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/flashcard/flashcard.json';
    }

    public function getName(): string
    {
        return 'flashcard_deck';
    }

    public function serialize(FlashcardDeck $flashcardDeck): array
    {
        return [
            'id' => $flashcardDeck->getId(),
            'name' => $flashcardDeck->getName(),
            'overview' => [
                'display' => $flashcardDeck->getShowOverview(),
                'message' => $flashcardDeck->getOverviewMessage(),
            ],
            'end' => [
                'display' => $flashcardDeck->getShowEndPage(),
                'message' => $flashcardDeck->getEndMessage(),
                'navigation' => $flashcardDeck->hasEndNavigation(),
            ],
            'cards' => $this->serializeCards($flashcardDeck)
        ];
    }

    private function serializeCards(FlashcardDeck $flashcardDeck): array
    {
        $cards = $flashcardDeck->getCards();
        $cardsData = [];

        foreach ($cards as $card) {
            $cardsData[] = [
                'id' => $card->getId(),
                'question' => $card->getQuestion(),
                'visibleContent' => $card->getVisibleContent(),
                'hiddenContent' => $card->getHiddenContent()
            ];
        }

        return $cardsData;
    }

    public function deserialize(array $data, FlashcardDeck $flashcardDeck): FlashcardDeck
    {
        $this->sipe('id', 'setId', $data, $flashcardDeck);
        $this->sipe('name', 'setName', $data, $flashcardDeck);

        if (!empty($data['overview'])) {
            $this->sipe('overview.display', 'setShowOverview', $data, $flashcardDeck);
            $this->sipe('overview.message', 'setOverviewMessage', $data, $flashcardDeck);
        }

        if (!empty($data['end'])) {
            $this->sipe('end.display', 'setShowEndPage', $data, $flashcardDeck);
            $this->sipe('end.message', 'setEndMessage', $data, $flashcardDeck);
            $this->sipe('end.navigation', 'setEndNavigation', $data, $flashcardDeck);
        }

        if (isset($data['cards'])) {
            $this->deserializeCards($data['cards'], $flashcardDeck);
        }

        return $flashcardDeck;
    }
    private function deserializeCards(array $cardsData, FlashcardDeck $flashcardDeck): void
    {
        $currentCardIds = [];

        foreach ($cardsData as $cardData) {
            $card = $flashcardDeck->getCardByUuid($cardData['id']) ?? new Flashcard();

            $this->sipe('question', 'setQuestion', $cardData, $card);
            $this->sipe('visibleContent', 'setVisibleContent', $cardData, $card);
            $this->sipe('hiddenContent', 'setHiddenContent', $cardData, $card);

            if (!$flashcardDeck->hasCard($card)) {
                $flashcardDeck->addCard($card);
            }
            $currentCardIds[] = $card->getId();
        }

        foreach ($flashcardDeck->getCards() as $existingCard) {
            if (!in_array($existingCard->getId(), $currentCardIds, true)) {
                $flashcardDeck->removeCard($existingCard);
            }
        }
    }
}
