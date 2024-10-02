<?php

namespace Claroline\OpenBadgeBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly OrganizationManager $organizationManager,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, BadgeClass::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, BadgeClass::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, BadgeClass::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, BadgeClass::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, BadgeClass::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        $badge->setCreatedAt(new \DateTime());
        $badge->setUpdatedAt(new \DateTime());

        $this->checkOrganization($badge);
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        if ($badge->getImage()) {
            $this->fileManager->linkFile(BadgeClass::class, $badge->getUuid(), $badge->getImage());
        }

        if ($badge->getPoster()) {
            $this->fileManager->linkFile(BadgeClass::class, $badge->getUuid(), $badge->getPoster());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        $badge->setUpdatedAt(new \DateTime());

        $this->checkOrganization($badge);
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            BadgeClass::class,
            $badge->getUuid(),
            $badge->getImage(),
            !empty($oldData['image']) ? $oldData['image'] : null
        );

        $this->fileManager->updateFile(
            BadgeClass::class,
            $badge->getUuid(),
            $badge->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        if ($badge->getImage()) {
            $this->fileManager->unlinkFile(BadgeClass::class, $badge->getUuid(), $badge->getImage());
        }

        if ($badge->getPoster()) {
            $this->fileManager->unlinkFile(BadgeClass::class, $badge->getUuid(), $badge->getPoster());
        }
    }

    /**
     * Auto link badge to the correct Organization if the user has not selected one.
     */
    private function checkOrganization(BadgeClass $badge): void
    {
        if (empty($badge->getIssuer())) {
            $organization = null;
            if ($badge->getWorkspace()) {
                $wsOrganizations = $badge->getWorkspace()->getOrganizations()->toArray();
                if (!empty($wsOrganizations)) {
                    $organization = $wsOrganizations[0];
                }
            } elseif ($this->tokenStorage->getToken()?->getUser() instanceof User) {
                $organization = $this->tokenStorage->getToken()?->getUser()->getMainOrganization();
            }

            if (!empty($organization)) {
                $badge->setIssuer($organization);
            } else {
                $badge->setIssuer($this->organizationManager->getDefault(true));
            }
        }
    }
}
