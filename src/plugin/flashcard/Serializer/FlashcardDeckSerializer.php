<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Entity\UserProgression;
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
            'id' => $flashcardDeck->getUuid(),
            'name' => $flashcardDeck->getName(),
            'draw' => $flashcardDeck->getDraw(),
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
                $unseenCards[] = $this->serializeCard($card);
            } elseif (!$userProgression->isSuccessful()) {
                $failedCards[] = $this->serializeCard($card);
            } else {
                $passedCards[] = $this->serializeCard($card);
            }
        }

        shuffle($unseenCards);
        shuffle($failedCards);
        shuffle($passedCards);

        return array_merge($unseenCards, $failedCards, $passedCards);
    }

    public function serializeCard(Flashcard $flashcard): array
    {
        return [
            'id' => $flashcard->getUuid(),
            'question' => $flashcard->getQuestion(),
            'visibleContent' => $flashcard->getVisibleContent(),
            'hiddenContent' => $flashcard->getHiddenContent(),
        ];
    }

    public function deserialize(array $data, FlashcardDeck $flashcardDeck): FlashcardDeck
    {
        $this->sipe('id', 'setUuid', $data, $flashcardDeck);
        $this->sipe('name', 'setName', $data, $flashcardDeck);
        $this->sipe('draw', 'setDraw', $data, $flashcardDeck);

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
