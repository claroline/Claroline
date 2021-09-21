<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class FunctionalLogSubscriber implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->translator = $translator;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE => ['logEvent', 10],
            ResourceEvents::RESOURCE_OPEN => ['logEvent', 10],
            ToolEvents::TOOL_OPEN => ['logEvent', 10],
        ];
    }

    public function logEvent(Event $event, string $eventName)
    {
        if ($event->getUser()) {
            $this->messageBus->dispatch(new CreateFunctionalLog(
                new \DateTime(),
                $eventName,
                $event->getMessage($this->translator), // this should not be done by the symfony event
                $event->getUser(),
                method_exists($event, 'getWorkspace') ? $event->getWorkspace() : null,
                method_exists($event, 'getResourceNode') ? $event->getResourceNode() : null
            ));
        }

        // Hack because of ToolEvents::TOOL_OPEN implements the DataConveyorEventInterface
        if (method_exists($event, 'setData')) {
            $event->setData([]);
        }
    }
}
