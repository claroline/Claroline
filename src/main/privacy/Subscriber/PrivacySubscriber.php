<?php

namespace Claroline\PrivacyBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrivacySubscriber implements EventSubscriberInterface
{
    const NAME = 'privacy';

    private SerializerProvider $serializer;
    private ObjectManager $objectManager;

    public function __construct(
        ObjectManager $objectManager,
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $firstPrivacy = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
        $data = $this->serializer->serialize($firstPrivacy);

        $event->setData([
            'parameters' => $data,
        ]);
    }

    /*public function onOpen(OpenToolEvent $event): void
    {
        // Récupére les données de la base de données
        $noteBookItems = $this->objectManager->getRepository(NoteBookItem::class)->findAll();

        // Transforme les données en un format approprié pour l'application React
        $data = array_map(function (NoteBookItem $noteBookItem) {
            return $this->noteBookItemSerializer->serialize($noteBookItem);
        }, $noteBookItems);

        // Envoie les données à l'application React
        $event->setData(['items' => $data]);
    }*/
}
