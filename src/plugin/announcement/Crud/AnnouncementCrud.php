<?php

namespace Claroline\AnnouncementBundle\Crud;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var AnnouncementManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        AnnouncementManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->manager = $manager;
    }

    public function preCreate(CreateEvent $event)
    {
        $announcement = $event->getObject();
        $options = $event->getOptions();
        $announcement->setAggregate($options['announcement_aggregate']);

        if (empty($announcement->getCreator())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                // only get authenticated user
                $announcement->setCreator($currentUser);
            }
        }
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var Announcement $announcement */
        $announcement = $event->getObject();
        $data = $event->getData();

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
    }

    public function preDelete(DeleteEvent $event)
    {
        $announcement = $event->getObject();
        $send = $this->om->getRepository(AnnouncementSend::class)->findBy(['announcement' => $announcement]);

        foreach ($send as $el) {
            $this->om->remove($el);
        }

        // delete scheduled task is any
        $this->manager->unscheduleMessage($announcement);
    }
}
