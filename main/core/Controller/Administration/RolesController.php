<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Form\RoleTranslationType;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('roles_management')")
 */
class RolesController extends Controller
{
    private $toolManager;
    private $roleManager;
    private $formFactory;
    private $request;

    /**
     * @DI\InjectParams({
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory" = @DI\Inject("form.factory"),
     *     "request"     = @DI\Inject("request"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ToolManager              $toolManager,
        RoleManager              $roleManager,
        FormFactory              $formFactory,
        Request                  $request,
        ObjectManager            $om
    ) {
        $this->toolManager = $toolManager;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->om = $om;
    }

    /**
     * @EXT\Route("/index", name="claro_admin_roles_index")
     * @EXT\Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @EXT\Route("/tools/index", name="claro_admin_tools_index")
     * @EXT\Template()
     *
     * @return array
     */
    public function toolsIndexAction()
    {
        $tools = $this->toolManager->getAdminTools();
        $roles = $this->roleManager->getPlatformNonAdminRoles();

        return array('tools' => $tools, 'roles' => $roles);
    }

    /**
     * @EXT\Route(
     *      "/bind/role/{role}/tool/{tool}", name="claro_admin_add_tool_to_role",
     *      options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param AdminTool $tool
     * @param Role      $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function addRoleToToolAction(AdminTool $tool, Role $role)
    {
        $this->toolManager->addRoleToAdminTool($tool, $role);

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/unbind/role/{role}/tool/{tool}", name="claro_admin_remove_tool_from_role",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param AdminTool $tool
     * @param Role      $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function removeRoleFromToolAction(AdminTool $tool, Role $role)
    {
        $this->toolManager->removeRoleFromAdminTool($tool, $role);

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/create/platform_role/form",
     *     name="claro_admin_create_platform_role_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return array
     */
    public function createPlatformRoleModalFormAction()
    {
        $form = $form = $this->formFactory->create(new RoleTranslationType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/create/platform_role",
     *     name="claro_admin_create_platform_role",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Roles:createPlatformRoleModalForm.html.twig")
     *
     * @return array
     */
    public function createPlatformRoleAction()
    {
        $form = $this->formFactory->create(new RoleTranslationType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $translationKey = $form->get('translationKey')->getData();
            $role = $this->roleManager->createPlatformRoleAction($translationKey);

            return new JsonResponse(
                array(
                    'id' => $role->getId(),
                    'maxUsers' => $role->getMaxUsers(),
                    'translationKey' => $role->getTranslationKey(),
                    'count' => 0,
                )
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/roles/list", name="platform_roles_list")
     * @EXT\Template("ClarolineCoreBundle:Administration/Roles:roleList.html.twig")
     *
     * @return array
     */
    public function roleListAction()
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $counts = [];

        foreach ($roles as $role) {
            $counts[$role->getName()] = $this->roleManager->countUsersByRoleIncludingGroup($role);
        }

        return array('roles' => $roles, 'counts' => $counts);
    }

    /**
     * @EXT\Route(
     *      "/remove/{role}",
     *      name="platform_roles_remove",
     *      options={"expose"=true}
     * )
     *
     * @param Role $role
     */
    public function removeRoleAction(Role $role)
    {
        $this->roleManager->remove($role);

        return new JsonResponse(
            array(
                'name' => $role->getName(),
                'limit' => $role->getMaxUsers(),
                'translationKey' => $role->getTranslationKey(),
            )
        );
    }

    /**
     * @EXT\Route(
     *      "/initialize/role/{role}",
     *      name="platform_role_initialize",
     *      options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function initializeRoleLimitAction(Role $role)
    {
        $this->roleManager->initializeLimit($role);

        return new JsonResponse(
            array(
                'name' => $role->getName(),
                'limit' => $role->getMaxUsers(),
                'translationKey' => $role->getTranslationKey(),
            )
        );
    }

    /**
     * @EXT\Route(
     *      "/role/{role}/increase/{amount}",
     *      name="platform_role_increase_limit",
     *      options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function increaseRoleMaxUsers(Role $role, $amount)
    {
        if ($amount < 0) {
            return new JsonResponse(
                array(
                    'name' => $role->getName(),
                    'limit' => $role->getMaxUsers(),
                    'translationKey' => $role->getTranslationKey(),
                    'error' => 'negative_amount_increased',
                ),
                500
            );
        }
        $this->roleManager->increaseRoleMaxUsers($role, $amount);

        return new JsonResponse(
            array(
                'name' => $role->getName(),
                'limit' => $role->getMaxUsers(),
                'translationKey' => $role->getTranslationKey(),
            )
        );
    }

    /**
     * @EXT\Route(
     *     "role/{role}/edit/name/{name}",
     *     name="platform_role_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function editRoleNameAction(Role $role, $name)
    {
        if (ctype_space($name)) {
            return new JsonResponse(
                array(
                    'name' => $role->getName(),
                    'limit' => $role->getMaxUsers(),
                    'translationKey' => $role->getTranslationKey(),
                ),
                500
            );
        }

        $role->setTranslationKey($name);
        $this->roleManager->edit($role);

        return new JsonResponse(
            array(
                'name' => $role->getName(),
                'limit' => $role->getMaxUsers(),
                'translationKey' => $role->getTranslationKey(),
            )
        );
    }

    /**
     * @EXT\Route(
     *     "role/{role}/invert_workspace_creation",
     *     name="platform_role_workspace_creation_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function invertPersonalWorkspaceCreationAction(Role $role)
    {
        $this->roleManager->invertWorkspaceCreation($role);

        return new JsonResponse(array(), 200);
    }
}
