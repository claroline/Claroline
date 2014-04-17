<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 *
 * Controller of the platform administration section (users, groups,
 * workspaces, platform settings, etc.).
 */
class AdministrationController extends Controller
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $toolManager;
    private $workspaceManager;
    private $workspaceTagManager;
    private $eventDispatcher;
    private $formFactory;
    private $analyticsManager;
    private $translator;
    private $request;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "analyticsManager"    = @DI\Inject("claroline.manager.analytics_manager"),
     *     "translator"          = @DI\Inject("translator"),
     *     "request"             = @DI\Inject("request")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        AnalyticsManager $analyticsManager,
        Translator $translator,
        Request $request
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->groupManager = $groupManager;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->analyticsManager = $analyticsManager;
        $this->translator = $translator;
        $this->request = $request;
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_index"
     * )
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('claro_admin_parameters_index'));
    }
}