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
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RolesController extends Controller
{
    private $toolManager;
    private $roleManager;
    private $formFactory;
    private $request;
    private $roleAdminTool;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "request"     = @DI\Inject("request"),
     *     "sc"          = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        RoleManager $roleManager,
        FormFactory $formFactory,
        Request $request,
        SecurityContextInterface $sc
    )
    {
        $this->toolManager   = $toolManager;
        $this->roleManager   = $roleManager;
        $this->formFactory   = $formFactory;
        $this->request       = $request;
        $this->roleAdminTool = $toolManager->getAdminToolByName('roles_management');
        $this->sc            = $sc;
    }

    /**
     * @EXT\Route("/index", name="claro_admin_roles_index")
     * @EXT\Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @EXT\Route("/roles/tools/index", name="claro_admin_tools_index")
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * @return array
     */
    public function toolsIndexAction()
    {
        $this->checkOpen();

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
     * @param Tool $tool
     * @param Role $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function addRoleToToolAction(AdminTool $tool, Role $role)
    {
        $this->checkOpen();

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
     * @param Tool $tool
     * @param Role $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function removeRoleFromToolAction(AdminTool $tool, Role $role)
    {
        $this->checkOpen();

        $this->toolManager->removeRoleFromAdminTool($tool, $role);

        return new Response('success');
    }

    /**
     * @EXT\Route("/create/platform_role/form", name="create_platform_role_form")
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * @return array
     */
    public function createPlatformRoleFormAction()
    {
        $this->checkOpen();

        $form = $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/create/platform_role", name="create_platform_role")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Roles:createPlatformRoleForm.html.twig")
     *
     * @return array
     */
    public function createPlatformRoleAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $translationKey = $form->get('translationKey')->getData();
            $this->roleManager->createPlatformRoleAction($translationKey);

            return $this->redirect($this->generateUrl('claro_admin_roles_index'));
        }

        return array('form' => $form->createView());
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->roleAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
} 