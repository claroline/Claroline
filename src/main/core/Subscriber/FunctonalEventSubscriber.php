<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\CoreBundle\Entity\Log\FunctionalLog;
use Claroline\CoreBundle\Event\CatalogEvents\FunctionalEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class FunctonalEventSubscriber implements EventSubscriberInterface
{
    private $translator;
    private $em;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FunctionalEvents::ADD_BADGE => 'logEvent',
            FunctionalEvents::REMOVE_BADGE => 'logEvent',
            FunctionalEvents::RESOURCE_EVALUATION => 'logEvent',
            FunctionalEvents::RESOURCE_OPEN => 'logEvent',
        ];
    }

    public function logEvent(Event $event, string $eventName)
    {
        $logEntry = new FunctionalLog();

        $logEntry->setUser($event->getUser());
        $logEntry->setDetails($event->getMessage($this->translator));
        $logEntry->setEvent($eventName);

        if (method_exists($event, 'getResourceNode')) {
            $logEntry->setResource($event->getResourceNode());
        }

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
