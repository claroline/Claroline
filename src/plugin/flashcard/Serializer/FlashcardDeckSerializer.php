<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FlashcardDeckSerializer
{
    use SerializerTrait;

    private ObjectManager $om;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass(): string
    {
        return FlashcardDeck::class;
    }

    public function getName(): string
    {
        return 'flashcard_deck';
    }

    public function serialize(FlashcardDeck $flashcardDeck): array
    {
        return [
            'id' => $flashcardDeck->getUuid(),
            'name' => $flashcardDeck->getName(),
            'showProgression' => $flashcardDeck->getShowProgression(),
            'customButtons' => $flashcardDeck->getCustomButtons(),
            'rightButtonLabel' => $flashcardDeck->getRightButtonLabel(),
            'wrongButtonLabel' => $flashcardDeck->getWrongButtonLabel(),
            'draw' => $flashcardDeck->getDraw(),
            'showLeitnerRules' => $flashcardDeck->getShowLeitnerRules(),
            'overview' => [
                'display' => $flashcardDeck->getShowOverview(),
                'message' => $flashcardDeck->getOverviewMessage(),
            ],
            'end' => [
                'display' => $flashcardDeck->getShowEndPage(),
                'message' => $flashcardDeck->getEndMessage(),
                'navigation' => $flashcardDeck->hasEndNavigation(),
            ],
            'cards' => $this->serializeCards($flashcardDeck),
        ];
    }

    private function serializeCards(FlashcardDeck $flashcardDeck): array
    {
        $cards = $flashcardDeck->getCards();

        $serializedCards = [];
        foreach ($cards as $card) {
            $serializedCards[] = $this->serializeCard($card);
        }

        return $serializedCards;
    }

    public function serializeCard(Flashcard $flashcard): array
    {
        return [
            'id' => $flashcard->getUuid(),
            'question' => $flashcard->getQuestion(),
            'visibleContent' => 'text' !== $flashcard->getVisibleContentType() ? json_decode($flashcard->getVisibleContent()) : $flashcard->getVisibleContent(),
            'hiddenContent' => 'text' !== $flashcard->getHiddenContentType() ? json_decode($flashcard->getHiddenContent()) : $flashcard->getHiddenContent(),
            'visibleContentType' => $flashcard->getVisibleContentType(),
            'hiddenContentType' => $flashcard->getHiddenContentType(),
        ];
    }

    public function deserialize(array $data, FlashcardDeck $flashcardDeck): FlashcardDeck
    {
        $this->sipe('id', 'setUuid', $data, $flashcardDeck);
        $this->sipe('name', 'setName', $data, $flashcardDeck);
        $this->sipe('showProgression', 'setShowProgression', $data, $flashcardDeck);
        $this->sipe('customButtons', 'setCustomButtons', $data, $flashcardDeck);
        $this->sipe('rightButtonLabel', 'setRightButtonLabel', $data, $flashcardDeck);
        $this->sipe('wrongButtonLabel', 'setWrongButtonLabel', $data, $flashcardDeck);
        $this->sipe('draw', 'setDraw', $data, $flashcardDeck);
        $this->sipe('showLeitnerRules', 'setShowLeitnerRules', $data, $flashcardDeck);

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

            $cardData['visibleContentType'] = $cardData['visibleContentType'] ?? 'text';
            $cardData['visibleContentType'] = '' != $cardData['visibleContentType'] ? $cardData['visibleContentType'] : 'text';
            if ('string' !== gettype($cardData['visibleContent'])) {
                $card->setVisibleContent(json_encode($cardData['visibleContent']));
            } else {
                $this->sipe('visibleContent', 'setVisibleContent', $cardData, $card);
            }
            $card->setVisibleContentType($cardData['visibleContentType']);

            $cardData['hiddenContentType'] = $cardData['hiddenContentType'] ?? 'text';
            $cardData['hiddenContentType'] = '' != $cardData['hiddenContentType'] ? $cardData['hiddenContentType'] : 'text';
            if ('string' !== gettype($cardData['hiddenContent'])) {
                $card->setHiddenContent(json_encode($cardData['hiddenContent']));
            } else {
                $this->sipe('hiddenContent', 'setHiddenContent', $cardData, $card);
            }
            $card->setHiddenContentType($cardData['hiddenContentType']);

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
