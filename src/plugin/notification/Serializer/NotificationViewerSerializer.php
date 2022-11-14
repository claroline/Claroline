<?php

namespace Icap\NotificationBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NotificationViewerSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var string */
    private $platformName;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var NotificationSerializer */
    private $notificationSerializer;

    /**
     * NotificationViewerSerializer constructor.
     */
    public function __construct(
        NotificationSerializer $notificationSerializer,
        EventDispatcherInterface $eventDispatcher,
        PlatformConfigurationHandler $configHandler,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->om = $om;
        $this->platformName = $configHandler->getParameter('name');
        $this->eventDispatcher = $eventDispatcher;
        $this->userSerializer = $userSerializer;
        $this->notificationSerializer = $notificationSerializer;
    }

    public function getName()
    {
        return 'notification_viewer';
    }

    public function getClass()
    {
        return NotificationViewer::class;
    }

    public function serialize(NotificationViewer $viewer)
    {
        $eventName = 'create_notification_item_'.$viewer->getNotification()->getActionKey();
        $event = new NotificationCreateDelegateViewEvent($viewer, $this->platformName);

        if ($this->eventDispatcher->hasListeners($eventName)) {
            $this->eventDispatcher->dispatch($event, $eventName);
        }

        return [
            'id' => $viewer->getId(),
            'read' => $viewer->getStatus(),
            'notification' => $this->notificationSerializer->serialize($viewer->getNotification()),
            'renderedText' => $event->getResponseContent(),
            'primaryAction' => $event->getPrimaryAction(),
            'text' => $event->getText(),
        ];
    }
}
