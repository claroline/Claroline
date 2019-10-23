<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\UserCreatedEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserCrud
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        //too many dependencies, simplify this when we can
        $this->container = $container;
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->finder = $container->get('Claroline\AppBundle\API\FinderProvider');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->toolManager = $container->get('claroline.manager.tool_manager');
        $this->mailManager = $container->get('claroline.manager.mail_manager');
        $this->userManager = $container->get('claroline.manager.user_manager');
        $this->dispatcher = $container->get('Claroline\AppBundle\Event\StrictDispatcher');
        $this->config = $container->get('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->cryptoManager = $container->get('claroline.manager.cryptography_manager');
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $user = $this->create($event->getObject(), $event->getOptions());

        $data = $event->getData();

        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $entity = $this->finder->get(Group::class)->findOneBy($group);
                $user->addGroup($entity);
            }
        }

        $this->om->persist($user);
        $this->om->flush();
    }

    public function create(User $user, $options = [], $extra = [])
    {
        $this->om->startFlushSuite();

        if (in_array(Options::FORCE_RANDOM_PUBLIC_URL, $options)) {
            $publicUrl = $user->getFirstName().'.'.$user->getLastName();
            $publicUrl = strtolower(str_replace(' ', '-', $publicUrl)).uniqid();
            $user->setPublicUrl($publicUrl);
        } else {
            $user->setPublicUrl($this->userManager->generatePublicUrl($user));
        }

        $addedTools = $this->toolManager->addRequiredToolsToUser($user, 0);
        $this->toolManager->addRequiredToolsToUser($user, 1);
        $roleUser = $this->roleManager->getRoleByName(PlatformRoles::USER);
        $groupUser = $this->om->getRepository(Group::class)->findOneByName(PlatformRoles::USER);

        if ($groupUser) {
            $user->addGroup($groupUser);
        } else {
            //maybe throw an exception ?
        }

        if (!$roleUser) {
            throw new \Exception('ROLE_USER does not exists');
        }

        $user->addRole($roleUser);

        //create default desktop tools
        $toolsRolesConfig = $this->toolManager->getUserDesktopToolsConfiguration($user);
        $this->toolManager->computeUserOrderedTools($user, $toolsRolesConfig, $addedTools);

        $this->roleManager->createUserRole($user);

        $user->setIsMailNotified($this->config->getParameter('auto_enable_email_redirect'));
        $user->setHideMailWarning($this->config->getParameter('auto_validate_email'));
        $user->setIsMailValidated($this->config->getParameter('auto_validate_email'));

        if ($this->mailManager->isMailerAvailable() && in_array(Options::SEND_EMAIL, $options)) {
            //send a validation by hash
            $mailValidation = $this->config->getParameter('registration_mail_validation');
            if (PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL === $mailValidation) {
                $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
                $user->setResetPasswordHash($password);
                $user->setIsEnabled(false);
                $this->mailManager->sendEnableAccountMessage($user);
            } elseif (PlatformDefaults::REGISTRATION_MAIL_VALIDATION_PARTIAL === $mailValidation) {
                //don't change anything
                $this->mailManager->sendCreationMessage($user);
            }
        }

        $this->om->persist($user);

        if (in_array(Options::ADD_NOTIFICATIONS, $options)) {
            //i'm not sure we can depend on that one
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

        $token = $this->container->get('security.token_storage')->getToken();

        if (null === $user->getMainOrganization()) {
            //we want a min organization
            if ($token && $token->getUser() instanceof User && $token->getUser()->getMainOrganization()) {
                $user->addOrganization($token->getUser()->getMainOrganization(), true);
            } else {
                $user->addOrganization($this->container->get('claroline.manager.organization.organization_manager')->getDefault(), true);
            }
        }

        if ($createWs) {
            $this->userManager->setPersonalWorkspace($user);
        }

        //we need this line for the log system
        //dispatch some events but they should be listening the same as we are imo.
        //something should be done for event listeners
        $this->dispatcher->dispatch('user_created_event', UserCreatedEvent::class, ['user' => $user]);
        $this->dispatcher->dispatch('log', 'Log\LogUserCreate', [$user]);
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
            $ws->setDisplayable(false);
            $this->om->persist($ws);
        }

        if ($userRole) {
            $this->om->remove($userRole);
        }
        $this->om->persist($user);
        $this->om->flush();

        //dispatch some events but they should be listening the same as we are imo.
        //something should be done for event listeners
        $this->dispatcher->dispatch('claroline_users_delete', 'GenericData', [[$user]]);
        $this->dispatcher->dispatch('log', 'Log\LogUserDelete', [$user]);
        $this->dispatcher->dispatch('delete_user', 'DeleteUser', [$user]);
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
