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
use Claroline\CoreBundle\Entity\Administration\Tool;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 *
 * Controller of the platform parameters section.
 */
class RolesController extends Controller
{
    private $toolManager;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        RoleManager $roleManager
    )
    {
        $this->toolManager = $toolManager;
        $this->roleManager = $roleManager;
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
     * @EXT\Route("/roles/tools/index", name="claro_admin_tools_index")
     * @EXT\Method("GET")
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
     * @param Tool $tool
     * @param Role $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function addRoleToTool(Tool $tool, Role $role)
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
     * @param Tool $tool
     * @param Role $role
     *
     * @return \Claroline\CoreBundle\Controller\Administration\Response
     */
    public function removeRoleFromTool(Tool $tool, Role $role)
    {
        $this->toolManager->removeRoleFromAdminTool($tool, $role);

        return new Response('success');
    }
} 