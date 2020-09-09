<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\CryptographyManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationCrud
{
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, ObjectManager $om, CryptographyManager $cryptoManager, Crud $crud)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->cryptoManager = $cryptoManager;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $organization = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $organization->addAdministrator($user);
        }
    }

    /**
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        $organization = $event->getObject();
        $key = $this->cryptoManager->generatePair();
        $key->setOrganization($organization);
        $this->om->persist($key);
        $this->om->flush();
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
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

    /**
     * @param PatchEvent $event
     */
    public function postPatch(PatchEvent $event)
    {
        $action = $event->getAction();
        $users = $event->getValue();
        $property = $event->getProperty();

        if ('administrator' === $property) {
            $roleAdminOrga = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION');
            if (Crud::COLLECTION_ADD === $action) {
                if (is_array($users)) {
                    /** @var User $user */
                    foreach ($users as $user) {
                        $user->addRole($roleAdminOrga);
                        $this->om->persist($user);
                    }
                } else {
                    $users->addRole($roleAdminOrga);
                    $this->om->persist($users);
                }
            } elseif (Crud::COLLECTION_REMOVE === $action) {
                if (is_array($users)) {
                    /** @var User $user */
                    foreach ($users as $user) {
                        $user->removeRole($roleAdminOrga);
                        $this->om->persist($user);
                    }
                } else {
                    $users->removeRole($roleAdminOrga);
                    $this->om->persist($users);
                }
            }

            $this->om->flush();
        }
    }
}
