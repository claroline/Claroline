<?php

namespace Claroline\FlashcardBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\FlashcardBundle\Entity\UserProgression;

class UserProgressionSerializer
{
    use SerializerTrait;

    private FlashcardDeckSerializer $flashcardDeckSerializer;

    public function __construct(FlashcardDeckSerializer $flashcardDeckSerializer)
    {
        $this->flashcardDeckSerializer = $flashcardDeckSerializer;
    }

    public function getClass(): string
    {
        return UserProgression::class;
    }

    public function getName(): string
    {
        return 'user_progression';
    }

    public function serialize(UserProgression $userProgression): array
    {
        return [
            'id' => $userProgression->getId(),
            'flashcard' => $this->flashcardDeckSerializer->serializeCard($userProgression->getFlashcard()),
            'isSuccessful' => $userProgression->isSuccessful(),
        ];
    }
}
