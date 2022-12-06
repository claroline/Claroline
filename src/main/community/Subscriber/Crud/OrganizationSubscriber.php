<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\CryptographyManager;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var CryptographyManager */
    private $cryptoManager;
    /** @var Crud */
    private $crud;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        CryptographyManager $cryptoManager,
        Crud $crud,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->cryptoManager = $cryptoManager;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Organization::class) => 'preCreate',
            Crud::getEventName('create', 'post', Organization::class) => 'postCreate',
            Crud::getEventName('update', 'post', Organization::class) => 'postUpdate',
            Crud::getEventName('delete', 'pre', Organization::class) => 'preDelete',
            Crud::getEventName('delete', 'post', Organization::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        $organization = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $organization->addManager($user);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();

        if ($organization->getPoster()) {
            $this->fileManager->linkFile(Organization::class, $organization->getUuid(), $organization->getPoster());
        }

        if ($organization->getThumbnail()) {
            $this->fileManager->linkFile(Organization::class, $organization->getUuid(), $organization->getThumbnail());
        }

        $key = $this->cryptoManager->generatePair();
        if ($key) {
            $key->setOrganization($organization);
            $this->om->persist($key);
            $this->om->flush();
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Organization::class,
            $organization->getUuid(),
            $organization->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Organization::class,
            $organization->getUuid(),
            $organization->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        if ($organization->isDefault()) {
            $event->block();

            return;
        }

        $keys = $this->om->getRepository(CryptographicKey::class)->findBy(['organization' => $organization]);

        foreach ($keys as $key) {
            $this->crud->delete($key);
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();

        if ($organization->getPoster()) {
            $this->fileManager->unlinkFile(Organization::class, $organization->getUuid(), $organization->getPoster());
        }

        if ($organization->getThumbnail()) {
            $this->fileManager->unlinkFile(Organization::class, $organization->getUuid(), $organization->getThumbnail());
        }
    }
}
