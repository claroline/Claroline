<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Icap\NotificationBundle\Manager\NotificationUserParametersManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var Crud */
    private $crud;
    /** @var RoleManager */
    private $roleManager;
    /** @var MailManager */
    private $mailManager;
    /** @var OrganizationManager */
    private $organizationManager;
    /** @var NotificationUserParametersManager */
    private $notificationManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        Crud $crud,
        RoleManager $roleManager,
        MailManager $mailManager,
        OrganizationManager $organizationManager,
        NotificationUserParametersManager $notificationManager,
        StrictDispatcher $dispatcher,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->config = $config;
        $this->crud = $crud;
        $this->roleManager = $roleManager;
        $this->mailManager = $mailManager;
        $this->organizationManager = $organizationManager;
        $this->notificationManager = $notificationManager;
        $this->dispatcher = $dispatcher;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', User::class) => 'preCreate',
            Crud::getEventName('update', 'pre', User::class) => 'preUpdate',
            Crud::getEventName('update', 'post', User::class) => 'postUpdate',
            Crud::getEventName('patch', 'pre', User::class) => 'prePatch',
            Crud::getEventName('patch', 'post', User::class) => 'postPatch',
            Crud::getEventName('delete', 'pre', User::class) => 'preDelete',
            Crud::getEventName('delete', 'post', User::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
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

        $user->setIsMailNotified(
            ArrayUtils::get($data, 'meta.mailNotified', $this->config->getParameter('auto_enable_email_redirect'))
        );
        $user->setIsMailValidated(
            ArrayUtils::get($data, 'meta.mailValidated', $this->config->getParameter('auto_validate_email'))
        );

        if ($this->mailManager->isMailerAvailable() && !in_array(Options::NO_EMAIL, $options)) {
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

        if (in_array(Options::ADD_NOTIFICATIONS, $options)) {
            // TODO : this shouldn't be done in the core. Create a CrudListener in notification plugin
            $notifications = $this->config->getParameter('auto_enable_notifications');
            $this->notificationManager->processUpdate($notifications, $user);
        }

        if (null === $user->getMainOrganization()) {
            $token = $this->tokenStorage->getToken();
            //we want a main organization
            if ($token && $token->getUser() instanceof User && $token->getUser()->getMainOrganization()) {
                $user->setMainOrganization($token->getUser()->getMainOrganization());
            } else {
                $user->setMainOrganization($this->organizationManager->getDefault());
            }
        }

        $this->om->endFlushSuite();
    }

    public function preUpdate(UpdateEvent $event)
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

    public function postUpdate(UpdateEvent $event)
    {
        $user = $event->getObject();

        if ($user->getPlainpassword()) {
            $this->dispatcher->dispatch(SecurityEvents::NEW_PASSWORD, NewPasswordEvent::class, [$user]);
        }
    }

    public function prePatch(PatchEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        // trying to add a new role to a user
        if (Crud::COLLECTION_ADD === $event->getAction() && $event->getValue() instanceof Role) {
            /** @var Role $role */
            $role = $event->getValue();

            if ($user->hasRole($role->getName()) || !$this->roleManager->validateRoleInsert($user, $role)) {
                $event->block();
            }
        }
    }

    public function postPatch(PatchEvent $event)
    {
        $user = $event->getObject();

        if ($event->getValue() instanceof Role) {
            $role = $event->getValue();

            $hasRoleFromGroup = $user->hasRole($role->getName(), true) && !$user->hasRole($role->getName(), false);
            if (!$hasRoleFromGroup) {
                if ('add' === $event->getAction()) {
                    $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [[$user], $role]);
                } elseif ('remove' === $event->getAction()) {
                    $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [[$user], $role]);
                }
            }
        } elseif ($event->getValue() instanceof Group) {
            foreach ($event->getValue()->getEntityRoles() as $role) {
                if (!$user->hasRole($role->getName(), false)) {
                    if ('add' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [[$user], $role]);
                    } elseif ('remove' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [[$user], $role]);
                    }
                }
            }
        }
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();
        $userRole = $this->roleManager->getUserRole($user->getUsername());

        //soft delete~
        $user->setRemoved(true);
        $user->setEmail('email#'.$user->getId());
        $user->setFirstName('firstname#'.$user->getId());
        $user->setLastName('lastname#'.$user->getId());
        $user->setPlainPassword(uniqid());
        $user->setUsername('username#'.$user->getId());
        $user->setAdministrativeCode('code#'.$user->getId());
        $user->setIsEnabled(false);

        $this->om->persist($user);

        if ($userRole) {
            // this would have been better in the postDelete event, but the username has been changed
            $this->crud->delete($userRole);
        }
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        if ($user->getPoster()) {
            $this->fileManager->unlinkFile(User::class, $user->getUuid(), $user->getPoster());
        }

        if ($user->getPicture()) {
            $this->fileManager->unlinkFile(User::class, $user->getUuid(), $user->getPicture());
        }
    }
}
