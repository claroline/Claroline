<?php

namespace  Icap\NotificationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.serializer.notification_viewer")
 * @DI\Tag("claroline.serializer")
 */
class NotificationViewerSerializer
{
    /**
     * ContactFinder constructor.
     *
     * @DI\InjectParams({
     *     "notificationSerializer" = @DI\Inject("claroline.serializer.notification"),
     *     "eventDispatcher"        = @DI\Inject("event_dispatcher"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "manager"                = @DI\Inject("icap.notification.manager"),
     *     "fileSerializer"         = @DI\Inject("claroline.serializer.public_file"),
     *     "userSerializer"         = @DI\Inject("claroline.serializer.user"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        NotificationSerializer $notificationSerializer,
        NotificationManager $manager,
        PlatformConfigurationHandler $configHandler,
        EventDispatcherInterface $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->notificationSerializer = $notificationSerializer;
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->platformName = $configHandler->getParameter('name');
        $this->om = $om;
        $this->userSerializer = $userSerializer;
        $this->fileSerializer = $fileSerializer;
    }

    public function serialize(NotificationViewer $viewer)
    {
        $doer = $this->om->getRepository(User::class)->find($viewer->getViewerId());

        $eventName = 'create_notification_item_'.$viewer->getNotification()->getActionKey();
        $event = new NotificationCreateDelegateViewEvent($viewer, $this->platformName);

        /* @var EventDispatcher $eventDispatcher */
        if ($this->eventDispatcher->hasListeners($eventName)) {
            $event = $this->eventDispatcher->dispatch($eventName, $event);
        }

        $data = [
            'id' => $viewer->getId(),
            'notification' => $this->notificationSerializer->serialize($viewer->getNotification()),
            'renderedText' => $doer->getUsername().' '.$this->render($viewer),
            'doer' => $this->userSerializer->serialize($doer, [Options::SERIALIZE_MINIMAL]),
            'primaryAction' => $event->getPrimaryAction(),
            'text' => $event->getText(),
        ];

        $data['doer']['meta']['picture'] = $this->serializePicture($doer);

        return $data;
    }

    /**
     * Serialize the user picture.
     *
     * @param User $user
     *
     * @return array|null
     */
    private function serializePicture(User $user)
    {
        if (!empty($user->getPicture())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $user->getPicture()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    public function render(NotificationViewer $viewer)
    {
        $eventName = 'create_notification_item_'.$viewer->getNotification()->getActionKey();
        $event = new NotificationCreateDelegateViewEvent($viewer, $this->platformName);

        /* @var EventDispatcher $eventDispatcher */
        if ($this->eventDispatcher->hasListeners($eventName)) {
            return $this->eventDispatcher->dispatch($eventName, $event)->getResponseContent();
        }
    }

    public function getClass()
    {
        return NotificationViewer::class;
    }
}
