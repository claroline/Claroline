<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CrudEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Security\PlatformRoles;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.crud.user")
 * @DI\Tag("claroline.crud")
 */
class UserCrud
{
    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->toolManager = $container->get('claroline.manager.tool_manager');
        $this->mailManager = $container->get('claroline.manager.mail_manager');
        $this->userManager = $container->get('claroline.manager.user_manager');
        $this->config = $container->get('claroline.config.platform_config_handler');
    }

    /**
     * @DI\Observe("crud_pre_create_object")
     *
     * @param \Claroline\CoreBundle\Event\CrudEvent $event
     */
    public function preCreate(CrudEvent $event)
    {
        if ($event->getObject() instanceof User) {
            $user = $event->getObject();

            $user->setPublicUrl($this->userManager->generatePublicUrl($user));
            $this->toolManager->addRequiredToolsToUser($user, 0);
            $this->toolManager->addRequiredToolsToUser($user, 1);
            $roleUser = $this->roleManager->getRoleByName(PlatformRoles::USER);
            $user->addRole($roleUser);
            $this->roleManager->createUserRole($user);
            if ($this->mailManager->isMailerAvailable()) {
                //send a validation by hash
                $mailValidation = $this->config->getParameter('registration_mail_validation');
                if ($mailValidation === PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL) {
                    $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
                    $user->setResetPasswordHash($password);
                    $user->setIsEnabled(false);
                    $this->mailManager->sendEnableAccountMessage($user);
                } elseif ($mailValidation === PlatformDefaults::REGISTRATION_MAIL_VALIDATION_PARTIAL) {
                    //don't change anything
                    $this->mailManager->sendCreationMessage($user);
                }
            }

            $this->userManager->setPersonalWorkspace($user);
        }
    }

    /**
     * @DI\Observe("crud_post_create_object")
     *
     * @param \Claroline\CoreBundle\Event\CrudEvent $event
     */
    public function postCreate(CrudEvent $event)
    {
        if ($event->getObject() instanceof User) {
        }
    }
}
