<?php

namespace Claroline\CoreBundle\Subscriber\Log;

use Claroline\CoreBundle\Entity\Log\FunctionalLog;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class FunctionalLogSubscriber implements EventSubscriberInterface
{
    private $translator;
    private $em;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::RESOURCE_EVALUATION => ['logEvent', 10],
            ResourceEvents::RESOURCE_OPEN => ['logEvent', 10],
            ToolEvents::TOOL_OPEN => ['logEvent', 10],
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
        } elseif (method_exists($event, 'getWorkspace')) {
            $logEntry->setWorkspace($event->getWorkspace());
        }
        if (method_exists($event, 'setData')) {
            $event->setData([]);
        }

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
