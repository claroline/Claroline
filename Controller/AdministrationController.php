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

    /**
     * @EXT\Route("delete/logo/{file}", name="claro_admin_delete_logo")
     *
     * @param $file
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteLogoAction($file)
    {
        try {
            $this->get('claroline.common.logo_service')->deleteLogo($file);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * @EXT\Route(
     *     "plugins",
     *     name="claro_admin_plugins"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function pluginListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $plugins = $em->getRepository('ClarolineCoreBundle:Plugin')->findAll();

        return array('plugins' => $plugins);
    }

    /**
     * @EXT\Route(
     *     "/plugin/{domain}/options",
     *     name="claro_admin_plugin_options"
     * )
     * @EXT\Method("GET")
     *
     * Redirects to the plugin management page.
     *
     * @param string $domain
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function pluginParametersAction($domain)
    {
        $eventName = "plugin_options_{$domain}";
        $event = $this->eventDispatcher->dispatch($eventName, 'PluginOptions', array());

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/logs/",
     *     name="claro_admin_logs_show",
     *     defaults={"page" = 1}
     * )
     * @EXT\Route(
     *     "/logs/{page}",
     *     name="claro_admin_logs_show_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param $page int The requested page number.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logListAction($page)
    {
        return $this->get('claroline.log.manager')->getAdminList($page);
    }

    /**
     * @EXT\Route(
     *     "/analytics/",
     *     name="claro_admin_analytics_show"
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics.html.twig")
     *
     * Displays platform analytics home page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsAction()
    {
        $lastMonthActions = $this->analyticsManager->getDailyActionNumberForDateRange();
        $mostViewedWS = $this->analyticsManager->topWSByAction(null, 'ws_tool_read', 5);
        $mostViewedMedia = $this->analyticsManager->topMediaByAction(null, 'resource_read', 5);
        $mostDownloadedResources = $this->analyticsManager->topResourcesByAction(null, 'resource_export', 5);
        $usersCount = $this->userManager->countUsersForPlatformRoles();

        return array(
            'barChartData' => $lastMonthActions,
            'usersCount' => $usersCount,
            'mostViewedWS' => $mostViewedWS,
            'mostViewedMedia' => $mostViewedMedia,
            'mostDownloadedResources' => $mostDownloadedResources
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/connections",
     *     name="claro_admin_analytics_connections"
     * )
     *
     * @EXT\Method({"GET", "POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_connections.html.twig")
     *
     * Displays platform analytics connections page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsConnectionsAction()
    {
        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_CONNECTIONS,
            array(),
            array(
                "range" => $this->analyticsManager->getDefaultRange(),
                "unique" => "false"
            )
        );

        $criteriaForm->handleRequest($this->request);
        $unique = false;
        $range = null;

        if ($criteriaForm->isValid()) {
            $range = $criteriaForm->get('range')->getData();
            $unique = $criteriaForm->get('unique')->getData() === 'true';
        }

        $actionsForRange = $this->analyticsManager
            ->getDailyActionNumberForDateRange($range, 'user_login', $unique);

        $connections = $actionsForRange;
        $activeUsers = $this->analyticsManager->getActiveUsers();

        return array(
            'connections' => $connections,
            'form_criteria' => $criteriaForm->createView(),
            'activeUsers' => $activeUsers
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/resources",
     *     name="claro_admin_analytics_resources"
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_resources.html.twig")
     *
     * Displays platform analytics resources page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsResourcesAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $wsCount = $this->workspaceManager->getNbWorkspaces();
        $resourceCount = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->countResourcesByType();

        return array(
            'wsCount' => $wsCount,
            'resourceCount' => $resourceCount
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/top/{topType}",
     *     name="claro_admin_analytics_top",
     *     defaults={"topType" = "top_users_connections"}
     * )
     *
     * @EXT\Method({"GET", "POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_top.html.twig")
     *
     * Displays platform analytics top activity page
     *
     *
     * @param Request $request
     * @param $topType
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsTopAction(Request $request, $topType)
    {

        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_TOP,
            array(),
            array(
                "top_type" => $topType,
                "top_number" => 30,
                "range" => $this->analyticsManager->getDefaultRange()
            )
        );

        $criteriaForm->handleRequest($request);

        $range = $criteriaForm->get('range')->getData();
        $topType = $criteriaForm->get('top_type')->getData();
        $max = $criteriaForm->get('top_number')->getData();
        $listData = $this->analyticsManager->getTopByCriteria($range, $topType, $max);

        return array(
            'form_criteria' => $criteriaForm->createView(),
            'list_data' => $listData
        );
    }

    /**
     * @EXT\Route(
     *    "registration/management",
     *    name="claro_admin_registration_management",
     *    defaults={"search"=""},
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "registration/management/search/{search}",
     *     name="claro_admin_registration_management_search",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * @param string search
     *
     * @return Response
     */
    public function registrationManagementAction($search)
    {
        if ($search === '') {
            $datas = $this->workspaceTagManager->getDatasForWorkspaceList(false);

            return array(
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'search' => ''
            );
        }
        $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, 1);

        return array('workspaces' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *    "registration/management/users",
     *    name="claro_admin_registration_management_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @param Workspace[] $workspaces
     *
     * @return Response
     */
    public function registrationManagementUserListAction(array $workspaces)
    {
        $pager = $this->userManager->getAllUsers(1);

        return array('workspaces' => $workspaces, 'users' => $pager, 'search' => '');
    }

    /**
     * @EXT\Route(
     *    "registration/management/groups",
     *    name="claro_admin_registration_management_groups",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @param Workspace[] $workspaces
     *
     * @return Response
     */
    public function registrationManagementGroupListAction(array $workspaces)
    {
        $pager = $this->groupManager->getGroups(1);

        return array('workspaces' => $workspaces, 'groups' => $pager, 'search' => '');
    }

    /**
     * @EXT\Route(
     *     "/registration/list/users/page/{page}",
     *     name="claro_users_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/registration/list/users/page/{page}/search/{search}",
     *     name="claro_users_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Renders the user list in a pager for registration.
     *
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function userListPagerAction($page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return array('users' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}",
     *     name="claro_groups_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}/search/{search}",
     *     name="claro_groups_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Renders the group list in a pager for registration.
     *
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function groupListPagerAction($page, $search)
    {
        $pager = $search === '' ?
            $this->groupManager->getGroups($page) :
            $this->groupManager->getGroupsByName($search, $page);

        return array('groups' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/users/{roleKey}",
     *    name="claro_admin_subscribe_users_to_workspaces",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param string      $roleKey
     * @param Workspace[] $workspaces
     * @param User[]      $users
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleUsersToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $users
    )
    {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($users, $role);
            }
        }

        $msg = '';

        foreach ($users as $user) {
            $msg .= $user->getFirstName() . ' ' . $user->getLastName() . ' ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role',
                array(),
                'platform'
            );
            $msg .= ' "' .
                $this->translator->trans(
                    $roleKey,
                    array(),
                    'platform'
                ) .
                '"-;-';
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/groups/{roleKey}",
     *    name="claro_admin_subscribe_groups_to_workspaces",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param string      $roleKey
     * @param Workspace[] $workspaces
     * @param Group[]     $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleGroupsToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $groups
    )
    {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($groups, $role);
            }
        }

        $msg = '';

        foreach ($groups as $group) {
            $msg .= '"' . $group->getName() . '" ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role_group',
                array(),
                'platform'
            );
            $msg .= ' "' .
                $this->translator->trans(
                    $roleKey,
                    array(),
                    'platform'
                ) .
                '"-;-';
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/roles/users",
     *    name="claro_admin_subscribe_users_to_one_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "roles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param Role[] $roles
     * @param User[] $users
     *
     * @return Response
     */
    public function subscribeMultipleUsersToOneWorkspaceAction(
        array $roles,
        array $users
    )
    {
        $msg = '';

        foreach ($users as $user) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($user, $role);
                $msg .= $user->getFirstName() . ' ' . $user->getLastName() . ' ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role',
                    array(),
                    'platform'
                );
                $msg .= ' "' .
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        array(),
                        'platform'
                    ) .
                    '"-;-';
            }
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/roles/groups",
     *    name="claro_admin_subscribe_groups_to_one_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "roles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param Role[] $roles
     * @param Group[] $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleGroupsToOneWorkspaceAction(
        array $roles,
        array $groups
    )
    {
        $msg = '';

        foreach ($groups as $group) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($group, $role);
                $msg .= '"' . $group->getName() . '" ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role_group',
                    array(),
                    'platform'
                );
                $msg .= ' "' .
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        array(),
                        'platform'
                    ) .
                    '"-;-';
            }
        }

        return new Response($msg, 200);
    }
}