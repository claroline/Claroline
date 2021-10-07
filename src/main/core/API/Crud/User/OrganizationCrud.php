<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Manager\CryptographyManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationCrud
{
    private $tokenStorage;
    private $om;
    private $cryptoManager;
    private $crud;
    private $dispatcher;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        CryptographyManager $cryptoManager,
        Crud $crud,
        StrictDispatcher $dispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->cryptoManager = $cryptoManager;
        $this->dispatcher = $dispatcher;
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
        $organization = $event->getObject();
        $key = $this->cryptoManager->generatePair();
        $key->setOrganization($organization);
        $this->om->persist($key);
        $this->om->flush();
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        if ($organization->isDefault()) {
            $event->block();

            // we can also throw an exception
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
            $roleAdminOrga = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION');
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
}
