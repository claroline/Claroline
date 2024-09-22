<?php

namespace Claroline\AnnouncementBundle\Subscriber\Crud;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AnnouncementManager $manager,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Announcement::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Announcement::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Announcement::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Announcement::class) => 'preDelete',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Announcement::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        /*$announcement->setCreatedAt(new \DateTime());
        $announcement->setUpdatedAt(new \DateTime());*/

        if (empty($announcement->getCreator())) {
            $currentUser = $this->tokenStorage->getToken()?->getUser();
            if ($currentUser instanceof User) {
                // only get authenticated user
                $announcement->setCreator($currentUser);
            }
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if ($announcement->getPoster()) {
            $this->fileManager->linkFile(Announcement::class, $announcement->getUuid(), $announcement->getPoster());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();
        $data = $event->getData();
        $oldData = $event->getOldData();

        // manage announce sending
        if (!empty($data['meta']) && !empty($data['meta']['notifyUsers']) && !empty($announcement->getRoles())) {
            switch ($data['meta']['notifyUsers']) {
                case 1: // send now
                    $this->manager->sendMessage($announcement, $announcement->getRoles());
                    break;
                case 2: // send at planned date
                    $scheduledDate = DateNormalizer::denormalize($data['meta']['notificationDate']);
                    $this->manager->scheduleMessage($announcement, $announcement->getRoles(), $scheduledDate);
                    break;
            }
        }

        $this->fileManager->updateFile(
            Announcement::class,
            $announcement->getUuid(),
            $announcement->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        // delete scheduled task if any
        $this->manager->unscheduleMessage($announcement);
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if (!in_array(Options::SOFT_DELETE, $event->getOptions()) && $announcement->getPoster()) {
            $this->fileManager->unlinkFile(Announcement::class, $announcement->getUuid(), $announcement->getPoster());
        }
    }
}
