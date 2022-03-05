<?php

namespace Claroline\OpenBadgeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var OrganizationManager */
    private $organizationManager;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        OrganizationManager $organizationManager,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->organizationManager = $organizationManager;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', BadgeClass::class) => 'preCreate',
            Crud::getEventName('create', 'post', BadgeClass::class) => 'postCreate',
            Crud::getEventName('update', 'pre', BadgeClass::class) => 'preUpdate',
            Crud::getEventName('update', 'post', BadgeClass::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', BadgeClass::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        $this->checkOrganization($badge);
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        if ($badge->getImage()) {
            $this->fileManager->linkFile(BadgeClass::class, $badge->getUuid(), $badge->getImage());
        }
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        $this->checkOrganization($badge);
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            BadgeClass::class,
            $badge->getUuid(),
            $badge->getImage(),
            !empty($oldData['image']) ? $oldData['image']['url'] : null
        );
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        if ($badge->getImage()) {
            $this->fileManager->unlinkFile(BadgeClass::class, $badge->getUuid(), $badge->getImage());
        }
    }

    /**
     * Auto link badge to the correct Organization if the user has not selected one.
     */
    private function checkOrganization(BadgeClass $badge)
    {
        if (empty($badge->getIssuer())) {
            $organization = null;
            if ($badge->getWorkspace()) {
                if (count($badge->getWorkspace()->getOrganizations()) > 1) {
                    $organization = $badge->getWorkspace()->getOrganizations()[0];
                }
            } elseif ($this->tokenStorage->getToken()->getUser() instanceof User) {
                $organization = $this->tokenStorage->getToken()->getUser()->getMainOrganization();
            }

            if (!empty($organization)) {
                $badge->setIssuer($organization);
            } else {
                $badge->setIssuer($this->organizationManager->getDefault(true));
            }
        }
    }
}
