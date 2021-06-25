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
use Claroline\LogBundle\Messenger\Security\Message\AddRoleMessage;
use Claroline\LogBundle\Messenger\Security\Message\RemoveRoleMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganizationCrud
{
    private $tokenStorage;
    private $om;
    private $cryptoManager;
    private $crud;
    private $messageBus;
    private $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        CryptographyManager $cryptoManager,
        Crud $crud,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->cryptoManager = $cryptoManager;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
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
                        $this->messageBus->dispatch(
                            new AddRoleMessage(
                                $user->getId(),
                                $this->tokenStorage->getToken()->getUser()->getId(),
                                $this->translator->trans('addRole', ['username' => $user->getUsername(), 'role' => $roleAdminOrga->getName()], 'security')
                            )
                        );
                    }
                } else {
                    $users->addRole($roleAdminOrga);
                    $this->om->persist($users);
                    $this->messageBus->dispatch(
                        new AddRoleMessage(
                            $users->getId(),
                            $this->tokenStorage->getToken()->getUser()->getId(),
                            $this->translator->trans('addRole', ['username' => $users->getUsername(), 'role' => $roleAdminOrga->getName()], 'security')
                        )
                    );
                }
            } elseif (Crud::COLLECTION_REMOVE === $action) {
                if (is_array($users)) {
                    /** @var User $user */
                    foreach ($users as $user) {
                        $user->removeRole($roleAdminOrga);
                        $this->om->persist($user);
                        $this->messageBus->dispatch(
                            new RemoveRoleMessage(
                                $user->getId(),
                                $this->tokenStorage->getToken()->getUser()->getId(),
                                $this->translator->trans('removeRole', ['username' => $user->getUsername(), 'role' => $roleAdminOrga->getName()], 'security')
                            )
                        );
                    }
                } else {
                    $users->removeRole($roleAdminOrga);
                    $this->om->persist($users);
                    $this->messageBus->dispatch(
                        new RemoveRoleMessage(
                            $users->getId(),
                            $this->tokenStorage->getToken()->getUser()->getId(),
                            $this->translator->trans('removeRole', ['username' => $users->getUsername(), 'role' => $roleAdminOrga->getName()], 'security')
                        )
                    );
                }
            }

            $this->om->flush();
        }
    }
}
