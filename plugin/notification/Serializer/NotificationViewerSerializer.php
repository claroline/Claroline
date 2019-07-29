<?php

namespace  Icap\NotificationBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.serializer.notification_viewer")
 * @DI\Tag("claroline.serializer")
 */
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
     *
     * @DI\InjectParams({
     *     "notificationSerializer" = @DI\Inject("claroline.serializer.notification"),
     *     "eventDispatcher"        = @DI\Inject("event_dispatcher"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "userSerializer"         = @DI\Inject("claroline.serializer.user"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param NotificationSerializer       $notificationSerializer
     * @param PlatformConfigurationHandler $configHandler
     * @param EventDispatcherInterface     $eventDispatcher
     * @param UserSerializer               $userSerializer
     * @param ObjectManager                $om
     */
    public function __construct(
        NotificationSerializer $notificationSerializer,
        PlatformConfigurationHandler $configHandler,
        EventDispatcherInterface $eventDispatcher,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->om = $om;
        $this->platformName = $configHandler->getParameter('name');
        $this->eventDispatcher = $eventDispatcher;
        $this->userSerializer = $userSerializer;
        $this->notificationSerializer = $notificationSerializer;
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
            $this->eventDispatcher->dispatch($eventName, $event);
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
