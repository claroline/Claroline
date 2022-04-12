<?php

namespace Claroline\AnnouncementBundle\Subscriber\Crud;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementEvent;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var AnnouncementManager */
    private $manager;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        AnnouncementManager $manager,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->manager = $manager;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Crud::getEventName('create', 'pre', Announcement::class) => 'preCreate',
            Crud::getEventName('create', 'post', Announcement::class) => 'postCreate',
            Crud::getEventName('update', 'post', Announcement::class) => 'postUpdate',
            Crud::getEventName('delete', 'pre', Announcement::class) => 'preDelete',
            Crud::getEventName('delete', 'post', Announcement::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if (empty($announcement->getCreator())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                // only get authenticated user
                $announcement->setCreator($currentUser);
            }
        }
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if ($announcement->getPoster()) {
            $this->fileManager->linkFile(Announcement::class, $announcement->getUuid(), $announcement->getPoster());
        }

        $this->dispatchAnnouncementEvent($announcement, 'announcement-create');
    }

    public function postUpdate(UpdateEvent $event)
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
            !empty($oldData['poster']) ? $oldData['poster']['url'] : null
        );

        $this->dispatchAnnouncementEvent($announcement, 'announcement-update');
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if (!in_array(Options::SOFT_DELETE, $event->getOptions())) {
            $send = $this->om->getRepository(AnnouncementSend::class)->findBy(['announcement' => $announcement]);
            foreach ($send as $el) {
                $this->om->remove($el);
            }
        }

        // delete scheduled task if any
        $this->manager->unscheduleMessage($announcement);

        $this->dispatchAnnouncementEvent($announcement, 'announcement-delete');
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();

        if (!in_array(Options::SOFT_DELETE, $event->getOptions()) && $announcement->getPoster()) {
            $this->fileManager->unlinkFile(Announcement::class, $announcement->getUuid(), $announcement->getPoster());
        }
    }

    /**
     * @deprecated
     */
    private function dispatchAnnouncementEvent(Announcement $announcement, $action)
    {
        $this->dispatcher->dispatch('log', LogAnnouncementEvent::class, [$announcement, $action]);
    }
}
