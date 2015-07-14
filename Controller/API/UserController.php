<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\ProfileCreationType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;

class UserController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskDecoderManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        AuthenticationManager $authenticationManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        MailManager $mailManager,
        PlatformConfigurationHandler $configHandler,
        Request $request,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskDecoderManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        ObjectManager $om
    )
    {
        $this->authenticationManager = $authenticationManager;
        $this->configHandler = $configHandler;
        $this->formFactory = $formFactory;
        $this->localeManager = $localeManager;
        $this->mailManager = $mailManager;
        $this->request = $request;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->toolManager = $toolManager;
        $this->toolMaskDecoderManager = $toolMaskDecoderManager;
        $this->userAdminTool = $this->toolManager->getAdminToolByName('user_management');
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getUsersAction()
    {
        return $this->userManager->getAll();
    }

    public function putUserAction()
    {
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');

        $profileType = new ProfileCreationType(
            $this->localeManager,
            array($roleUser),
            true,
            $this->authenticationManager->getDrivers()
        );

        $form = $this->formFactory->create($profileType);
        $form->handleRequest($this->request);
        $roles = $form->get('platformRoles')->getData();
        $unavailableRoles = $this->roleManager->validateNewUserRolesInsert($roles);

        if ($form->isValid() && count($unavailableRoles) === 0) {
            $user = $form->getData();
            $this->userManager->createUser($user, true, $roles);

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles,
        );
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getUserAction($slug)
    {
        $user = $this->userManager->getUserByUsername($slug);

        return $user;
    }

    public function deleteUserAction($slug)
    {

    }
}
