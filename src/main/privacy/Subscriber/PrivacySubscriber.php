<?php

namespace Claroline\PrivacyBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\PrivacyBundle\Entity\Privacy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrivacySubscriber implements EventSubscriberInterface
{
    const NAME = 'privacy';

    private SerializerProvider $privacySerializer;
    private ObjectManager $objectManager;

    public function __construct(
        ObjectManager $objectManager,
        SerializerProvider $privacySerializer
    ) {
        $this->privacySerializer = $privacySerializer;
        $this->objectManager = $objectManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $firstPrivacy = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);
        $data = $this->privacySerializer->serialize($firstPrivacy);

        $event->setData([
            'parameters' => $data,
        ]);
    }
}
