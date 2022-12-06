<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Manager\CryptographyManager;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $om;
    private $cryptoManager;
    private $crud;
    private $dispatcher;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        CryptographyManager $cryptoManager,
        Crud $crud,
        StrictDispatcher $dispatcher,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->cryptoManager = $cryptoManager;
        $this->dispatcher = $dispatcher;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Organization::class) => 'preCreate',
            Crud::getEventName('create', 'post', Organization::class) => 'postCreate',
            Crud::getEventName('update', 'post', Organization::class) => 'postUpdate',
            Crud::getEventName('patch', 'post', Organization::class) => 'postPatch',
            Crud::getEventName('delete', 'pre', Organization::class) => 'preDelete',
            Crud::getEventName('delete', 'post', Organization::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        $organization = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $organization->addAdministrator($user);
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

    public function postPatch(PatchEvent $event): void
    {
        $action = $event->getAction();
        $property = $event->getProperty();

        if (is_array($event->getValue())) {
            $users = $event->getValue();
        } else {
            $users = [$event->getValue()];
        }

        if ('administrator' === $property) {
            $roleAdminOrga = $this->om->getRepository(Role::class)->findOneByName('ROLE_ADMIN_ORGANIZATION');
            if (Crud::COLLECTION_ADD === $action) {
                /** @var User $user */
                foreach ($users as $user) {
                    $user->addRole($roleAdminOrga);
                    $this->om->persist($user);
                }

                $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [$users, $roleAdminOrga]);
            } elseif (Crud::COLLECTION_REMOVE === $action) {
                /** @var User $user */
                foreach ($users as $user) {
                    $user->removeRole($roleAdminOrga);
                    $this->om->persist($user);
                }

                $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [$users, $roleAdminOrga]);
            }

            $this->om->flush();
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
