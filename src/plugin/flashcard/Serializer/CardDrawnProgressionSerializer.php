<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;

class CardDrawnProgressionSerializer
{
    use SerializerTrait;

    private FlashcardDeckSerializer $flashcardDeckSerializer;

    public function __construct(FlashcardDeckSerializer $flashcardDeckSerializer)
    {
        $this->flashcardDeckSerializer = $flashcardDeckSerializer;
    }

    public function getClass(): string
    {
        return CardDrawnProgression::class;
    }

    public function getName(): string
    {
        return 'card_drawn_progression';
    }

    public function serialize(CardDrawnProgression $cardDrawnProgression): array
    {
        return [
            'id' => $cardDrawnProgression->getId(),
            'flashcard' => $this->flashcardDeckSerializer->serializeCard($cardDrawnProgression->getFlashcard()),
            'isSuccessful' => $cardDrawnProgression->isSuccessful(),
            'successCount' => $cardDrawnProgression->getSuccessCount(),
        ];
    }
}
