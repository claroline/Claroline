<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserCrud
{
    /** @var ContainerInterface */
    private $container;

    /** @var TokenStorageInterface */
    private $tokenStorage;
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

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->tokenStorage = $container->get('security.token_storage');
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->config = $container->get('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->mailManager = $container->get('claroline.manager.mail_manager');
        $this->userManager = $container->get('claroline.manager.user_manager');
        $this->organizationManager = $container->get('claroline.manager.organization.organization_manager');
    }

    /**
     * @param CreateEvent $event
     */
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

    public function create(User $user, $options = [], $extra = [])
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
            $nManager = $this->container->get('Icap\NotificationBundle\Manager\NotificationUserParametersManager');
            $notifications = $this->config->getParameter('auto_enable_notifications');
            $nManager->processUpdate($notifications, $user);
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
            $this->userManager->setPersonalWorkspace($user);
        }

        $this->om->endFlushSuite();

        return $user;
    }

    /**
     * @param DeleteEvent $event
     */
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

    /**
     * @param UpdateEvent $event
     */
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
}
