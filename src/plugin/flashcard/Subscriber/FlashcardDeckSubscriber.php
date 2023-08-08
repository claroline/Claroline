<?php

namespace Claroline\FlashcardBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;

class FlashcardDeckSubscriber implements EventSubscriberInterface
{
    private SerializerProvider $serializer;

    public function __construct(
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.flashcard.load' => 'onLoad'
        ];
    }

    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var FlashcardDeck $flashcardDeck */
        $flashcardDeck = $event->getResource();

        $event->setData([
            'flashcardDeck' => $this->serializer->serialize($flashcardDeck),
        ]);
        $event->stopPropagation();
    }
}
