<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class FunctionalLogSubscriber implements EventSubscriberInterface
{
    private $translator;
    private $om;

    public function __construct(ObjectManager $om, TranslatorInterface $translator)
    {
        $this->om = $om;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::RESOURCE_EVALUATION => ['logEvent', 10],
            ResourceEvents::RESOURCE_OPEN => ['logEvent', 10],
        ];
    }

    public function logEvent(Event $event, string $eventName)
    {
        if ($event->getUser()) {
            // only create log for authenticated users
            $logEntry = new FunctionalLog();

            $logEntry->setUser($event->getUser());
            $logEntry->setDetails($event->getMessage($this->translator));
            $logEntry->setEvent($eventName);

            if (method_exists($event, 'getResourceNode')) {
                $logEntry->setResource($event->getResourceNode());
            } elseif (method_exists($event, 'getWorkspace')) {
                $logEntry->setWorkspace($event->getWorkspace());
            }

            $this->om->persist($logEntry);
            $this->om->flush();
        }

        if (method_exists($event, 'setData')) {
            $event->setData([]);
        }
    }
}
