<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;

class FlashcardDeckSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return FlashcardDeck::class;
    }

    public function serialize(FlashcardDeck $flashcardDeck): array
    {
        return [
            'id' => $flashcardDeck->getUuid(),
            'autoId' => $flashcardDeck->getId(),
            'name' => $flashcardDeck->getName(),
            'cards' => $flashcardDeck->getCards(),
        ];
    }

    public function deserialize(array $data, FlashcardDeck $flashcardDeck): FlashcardDeck
    {
        $this->sipe('id', 'setUuid', $data, $flashcardDeck);
        $this->sipe('name', 'setName', $data, $flashcardDeck);
        $this->sipe('cards', 'setCards', $data, $flashcardDeck);

        return $flashcardDeck;
    }
}
