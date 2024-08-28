<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Manager\MailManager;
use Claroline\CoreBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly Crud $crud,
        private readonly RoleManager $roleManager,
        private readonly MailManager $mailManager,
        private readonly OrganizationManager $organizationManager,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, User::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, User::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, User::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, User::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_PATCH, User::class) => 'postPatch',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, User::class) => 'preDelete',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, User::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();
        $options = $event->getOptions();
        $data = $event->getData();

        $this->om->startFlushSuite();

        if (empty($user->getUsername())) {
            $user->setUsername($user->getEmail());
        }

        if (empty($user->getLocale())) {
            $user->setLocale(
                $this->config->getParameter('locales.default')
            );
        }

        // add default roles and groups
        $this->roleManager->createUserRole($user);

        $groupUser = $this->om->getRepository(Group::class)->findOneBy(['name' => PlatformRoles::USER]);
        if ($groupUser) {
            $user->addGroup($groupUser);
        }

        $defaultRole = $this->config->getParameter('registration.default_role') ?? PlatformRoles::USER;
        $roleUser = $this->roleManager->getRoleByName($defaultRole);
        if ($roleUser) {
            $user->addRole($roleUser);
        }

        $user->setMailNotified(
            ArrayUtils::get($data, 'meta.mailNotified', $this->config->getParameter('auto_enable_email_redirect'))
        );
        $user->setMailValidated(
            ArrayUtils::get($data, 'meta.mailValidated', $this->config->getParameter('auto_validate_email'))
        );

        if (!in_array(Options::NO_EMAIL, $options)) {
            // send a validation by hash
            $mailValidation = $this->config->getParameter('registration.validation');
            if (PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL === $mailValidation) {
                $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
                $user->setResetPasswordHash($password);
                $user->setIsEnabled(false);
                $this->mailManager->sendEnableAccountMessage($user);
            } elseif (PlatformDefaults::REGISTRATION_MAIL_VALIDATION_PARTIAL === $mailValidation) {
                // don't change anything
                $this->mailManager->sendCreationMessage($user);
            }
        }

        $this->om->persist($user);

        if (empty($user->getMainOrganization())) {
            $token = $this->tokenStorage->getToken();
            // we want a main organization
            if ($token && $token->getUser() instanceof User && $token->getUser()->getMainOrganization()) {
                $user->setMainOrganization($token->getUser()->getMainOrganization());
            } else {
                $user->setMainOrganization($this->organizationManager->getDefault());
            }
        }

        $this->om->endFlushSuite();
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if ($user->getPoster()) {
            $this->fileManager->linkFile(User::class, $user->getUuid(), $user->getPoster());
        }

        if ($user->getThumbnail()) {
            $this->fileManager->linkFile(User::class, $user->getUuid(), $user->getThumbnail());
        }

        if ($user->getPicture()) {
            $this->fileManager->linkFile(User::class, $user->getUuid(), $user->getPicture());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        $oldData = $event->getOldData();
        $user = $event->getObject();

        if (!empty($oldData) && $oldData['username'] !== $user->getUsername()) {
            $userRole = $this->roleManager->getUserRole($oldData['username']);
            if ($userRole) {
                $this->roleManager->renameUserRole($userRole, $user->getUsername());
            }
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            User::class,
            $user->getUuid(),
            $user->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            User::class,
            $user->getUuid(),
            $user->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );

        $this->fileManager->updateFile(
            User::class,
            $user->getUuid(),
            $user->getPicture(),
            !empty($oldData['picture']) ? $oldData['picture'] : null
        );

        if ($user->getPlainpassword()) {
            $event = new NewPasswordEvent($user);
            $this->eventDispatcher->dispatch($event, SecurityEvents::NEW_PASSWORD);
        }
    }

    public function postPatch(PatchEvent $event): void
    {
        $user = $event->getObject();

        if ($event->getValue() instanceof Role) {
            $role = $event->getValue();

            $hasRoleFromGroup = $user->hasRole($role->getName(), true) && !$user->hasRole($role->getName(), false);
            if (!$hasRoleFromGroup) {
                if ('add' === $event->getAction()) {
                    $event = new AddRoleEvent([$user], $role);
                    $this->eventDispatcher->dispatch($event, AddRoleEvent::class);
                } elseif ('remove' === $event->getAction()) {
                    $event = new RemoveRoleEvent([$user], $role);
                    $this->eventDispatcher->dispatch($event, RemoveRoleEvent::class);
                }
            }
        } elseif ($event->getValue() instanceof Group) {
            foreach ($event->getValue()->getEntityRoles() as $role) {
                if (!$user->hasRole($role->getName(), false)) {
                    if ('add' === $event->getAction()) {
                        $event = new AddRoleEvent([$user], $role);
                        $this->eventDispatcher->dispatch($event, AddRoleEvent::class);
                    } elseif ('remove' === $event->getAction()) {
                        $event = new RemoveRoleEvent([$user], $role);
                        $this->eventDispatcher->dispatch($event, RemoveRoleEvent::class);
                    }
                }
            }
        }
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();
        $userRole = $this->roleManager->getUserRole($user->getUsername());

        // soft delete~
        $user->setRemoved(true);
        $user->setEmail('email'.$user->getId().'@deleted.com');
        $user->setFirstName('firstname#'.$user->getId());
        $user->setLastName('lastname#'.$user->getId());
        $user->setPlainPassword(uniqid());
        $user->setUsername('username#'.$user->getId());
        $user->setAdministrativeCode('code#'.$user->getId());
        $user->setIsEnabled(false);

        $this->om->persist($user);

        if ($userRole) {
            // this would have been better in the postDelete event, but the username has been changed
            $this->crud->delete($userRole, [Crud::NO_PERMISSIONS]);
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if ($user->getPoster()) {
            $this->fileManager->unlinkFile(User::class, $user->getUuid(), $user->getPoster());
        }

        if ($user->getThumbnail()) {
            $this->fileManager->unlinkFile(User::class, $user->getUuid(), $user->getThumbnail());
        }

        if ($user->getPicture()) {
            $this->fileManager->unlinkFile(User::class, $user->getUuid(), $user->getPicture());
        }
    }
}
