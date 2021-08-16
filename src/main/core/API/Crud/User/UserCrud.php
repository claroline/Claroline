<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Icap\NotificationBundle\Manager\NotificationUserParametersManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Authenticator */
    private $authenticator;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var RoleManager */
    private $roleManager;
    /** @var MailManager */
    private $mailManager;
    /** @var UserManager */
    private $userManager;
    /** @var OrganizationManager */
    private $organizationManager;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var NotificationUserParametersManager */
    private $notificationManager;
    /** @var StrictDispatcher */
    private $dispatcher;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        RoleManager $roleManager,
        MailManager $mailManager,
        UserManager $userManager,
        OrganizationManager $organizationManager,
        WorkspaceManager $workspaceManager,
        NotificationUserParametersManager $notificationManager,
        StrictDispatcher $dispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->om = $om;
        $this->config = $config;
        $this->roleManager = $roleManager;
        $this->mailManager = $mailManager;
        $this->userManager = $userManager;
        $this->organizationManager = $organizationManager;
        $this->workspaceManager = $workspaceManager;
        $this->notificationManager = $notificationManager;
        $this->dispatcher = $dispatcher;
    }

    public function preCreate(CreateEvent $event)
    {
        $restrictions = $this->config->getParameter('restrictions') ?? [];
        if (isset($restrictions['users']) && isset($restrictions['max_users']) && $restrictions['users'] && $restrictions['max_users']) {
            $usersCount = $this->userManager->countEnabledUsers();
            if ($usersCount >= $restrictions['max_users']) {
                throw new AccessDeniedException();
            }
        }

        $user = $this->create($event->getObject(), $event->getOptions());

        $this->om->persist($user);
        $this->om->flush();
    }

    public function create(User $user, $options = [])
    {
        $this->om->startFlushSuite();

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

        $mailValidated = $user->isMailValidated() ?? $this->config->getParameter('auto_validate_email');
        $user->setIsMailNotified($this->config->getParameter('auto_enable_email_redirect'));
        $user->setIsMailValidated($mailValidated);

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

        $createWs = false;

        if (!in_array(Options::NO_PERSONAL_WORKSPACE, $options)) {
            foreach ($user->getEntityRoles() as $role) {
                if ($role->getPersonalWorkspaceCreationEnabled()) {
                    $createWs = true;
                }
            }
        }

        if (null === $user->getMainOrganization()) {
            $token = $this->tokenStorage->getToken();
            //we want a min organization
            if ($token && $token->getUser() instanceof User && $token->getUser()->getMainOrganization()) {
                $user->addOrganization($token->getUser()->getMainOrganization(), true);
            } else {
                $user->addOrganization($this->organizationManager->getDefault(), true);
            }
        }

        if ($createWs) {
            $this->workspaceManager->setPersonalWorkspace($user);
        }

        $this->om->endFlushSuite();

        return $user;
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
        $user->setPublicUrl('removed#'.$user->getId());
        $user->setAdministrativeCode('code#'.$user->getId());
        $user->setIsEnabled(false);

        // keeping the user's workspace with its original code
        // would prevent creating a user with the same username
        // todo: workspace deletion should be an option
        $ws = $user->getPersonalWorkspace();

        if ($ws) {
            $ws->setCode($ws->getCode().'#deleted_user#'.$user->getId());
            $ws->setHidden(true);
            $this->om->persist($ws);
        }

        if ($userRole) {
            $this->om->remove($userRole);
        }

        $this->om->persist($user);
        $this->om->flush();
    }

    public function preUpdate(UpdateEvent $event)
    {
        $oldData = $event->getOldData();
        $user = $event->getObject();

        if (!empty($oldData) && $oldData['username'] !== $user->getUsername()) {
            $userRole = $this->roleManager->getUserRole($oldData['username']);
            if ($userRole) {
                $this->roleManager->renameUserRole($userRole, $user->getUsername());
                // TODO : rename personal WS if user is renamed
            }
            // TODO: create if not exist
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
        /** @var User $user */
        $user = $event->getObject();
        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if ($event->getValue() instanceof Role) {
            // refresh token to get updated roles if the current user has changes in his roles
            if ($this->authenticator->isAuthenticatedUser($user)) {
                $this->authenticator->createToken($currentUser);
            }

            if ('add' === $event->getAction()) {
                $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [[$event->getObject()], $event->getValue()]);
            } elseif ('remove' === $event->getAction()) {
                $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [[$event->getObject()], $event->getValue()]);
            }
        }
    }
}
