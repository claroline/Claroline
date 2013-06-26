<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\AdminAnalyticsConnectionsType;
use Claroline\CoreBundle\Form\AdminAnalyticsTopType;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Form\GroupSettingsType;
use Claroline\CoreBundle\Form\PlatformParametersType;
use Claroline\CoreBundle\Form\ImportUserType;
use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Event\LogUserDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupCreateEvent;
use Claroline\CoreBundle\Library\Event\LogGroupAddUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupRemoveUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupUpdateEvent;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Controller of the platform administration section (users, groups,
 * workspaces, platform settings, etc.).
 */
class AdministrationController extends Controller
{
    const USER_PER_PAGE = 40;
    const GROUP_PER_PAGE = 40;

    /**
     * @Template("ClarolineCoreBundle:Administration:index.html.twig")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "/user/form",
     *     name="claro_admin_user_creation_form"
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $roles = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findPlatformRoles($user);
        $form = $this->createForm(new ProfileType($roles));

        return array('form_complete_user' => $form->createView());
    }

    /**
     * @Route(
     *     "/user",
     *     name="claro_admin_create_user"
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Administration:userCreationForm.html.twig")
     *
     * Creates an user (and its personal workspace) and redirects to the user list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createUserAction()
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $roles = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findPlatformRoles($user);
        $form = $this->get('form.factory')->create(new ProfileType($roles), new User());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            foreach ($newRoles as $role) {
                $user->addRole($role);
            }
            $this->get('claroline.user.creator')->create($user);

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        return array('form_complete_user' => $form->createView());
    }

    /**
     * @Route(
     *     "/users",
     *     name="claro_admin_multidelete_user",
     *     options = {"expose"=true}
     * )
     * @Method("DELETE")
     *
     * Removes many users from the platform.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUsersAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->getDoctrine()->getManager();

            foreach ($params['ids'] as $userId) {
                $user = $em->getRepository('ClarolineCoreBundle:User')
                    ->find($userId);

                $em->remove($user);
                $em->flush();

                $log = new LogUserDeleteEvent($user);
                $this->get('event_dispatcher')->dispatch('log', $log);
            }

            $em->flush();
        }

        return new Response('user(s) removed', 204);
    }

    /**
     * @Route(
     *     "users/page/{page}",
     *     name="claro_admin_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     *
     * @Route(
     *     "users/page/{page}/search/{search}",
     *     name="claro_admin_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Displays the platform user list.
     */
    public function userListAction($page, $search)
    {
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User');
        $query = ($search == "") ? $repo->findAll(true): $repo->findByName($search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @Route(
     *     "/groups/page/{page}",
     *     name="claro_admin_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @Method("GET")
     *
     * @Route(
     *     "groups/page/{page}/search/{search}",
     *     name="claro_admin_group_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Returns the platform group list.
     */
    public function groupListAction($page, $search)
    {
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Group');
        $query = ($search == "") ? $repo->findAll(true): $repo->findByName($search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @Route(
     *     "/group/{groupId}/users/page/{page}",
     *     name="claro_admin_user_of_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @Method("GET")
     *
     * @Route(
     *     "/group/{groupId}/users/page/{page}/search/{search}",
     *     name="claro_admin_user_of_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Returns the users of a group.
     */
    public function usersOfGroupListAction($groupId, $page, $search)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);
        $repo = $em->getRepository('ClarolineCoreBundle:User');
        $query = ($search == "") ?
            $repo->findByGroup($group, true):
            $repo->findByNameAndGroup($search, $group, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search, 'group' => $group);
    }

    /**
     * @Route(
     *     "/group/add/{groupId}/page/{page}",
     *     name="claro_admin_outside_of_group_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @Method("GET")
     *
     * @Route(
     *     "/group/add/{groupId}/page/{page}/search/{search}",
     *     name="claro_admin_outside_of_group_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Displays the user list with a control allowing to add them to a group.
     */
    public function outsideOfGroupUserListAction($groupId, $page, $search)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);
        $repo = $em->getRepository('ClarolineCoreBundle:User');
        $query = ($search == "") ?
            $repo->findGroupOutsiders($group, true):
            $repo->findGroupOutsidersByName($group, $search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search, 'group' => $group);
    }

    /**
     * @Route(
     *     "/group/form",
     *     name="claro_admin_group_creation_form"
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupCreationFormAction()
    {
        $form = $this->createForm(new GroupType(), new Group());

        return array('form_group' => $form->createView());
    }

    /**
     * @Route(
     *     "/group",
     *     name="claro_admin_create_group"
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Administration:groupCreationForm.html.twig")
     *
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createGroupAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new GroupType(), new Group());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $group = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $userRole = $em->getRepository('ClarolineCoreBundle:Role')
                ->findOneByName('ROLE_USER');
            $group->setPlatformRole($userRole);
            $em->persist($group);
            $em->flush();

            $log = new LogGroupCreateEvent($group);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array('form_group' => $form->createView());
    }

    /**
     * @Route(
     *     "/group/{groupId}/users",
     *     name="claro_admin_multiadd_user_to_group",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("PUT")
     *
     * Adds multiple user to a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function addUsersToGroupAction($groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $this->get('request')->query->all();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);
        $users = array();

        if (isset($params['userIds'])) {
            foreach ($params['userIds'] as $userId) {
                $user = $em->getRepository('ClarolineCoreBundle:User')
                    ->find($userId);

                if ($user !== null) {
                    $group->addUser($user);
                    $users[] = $user;
                }
            }
        }

        $em->persist($group);
        $em->flush();

        foreach ($users as $user) {
            $log = new LogGroupAddUserEvent($group, $user);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/group/{groupId}/users",
     *     name="claro_admin_multidelete_user_from_group",
     *     options={"expose"=true},
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("DELETE")
     *
     * Removes users from a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function deleteUsersFromGroupAction($groupId)
    {
        $params = $this->get('request')->query->all();
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);

        $users = array();
        if (isset($params['userIds'])) {
            foreach ($params['userIds'] as $userId) {
                $user = $em->getRepository('ClarolineCoreBundle:User')
                    ->find($userId);
                $group->removeUser($user);
                $em->persist($group);

                $users[] = $user;
            }
        }

        $em->flush();

        foreach ($users as $user) {
            $log = new LogGroupRemoveUserEvent($group, $user);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        return new Response('user removed', 204);
    }

    /**
     * @Route(
     *     "/groups",
     *     name="claro_admin_multidelete_group",
     *     options={"expose"=true}
     * )
     * @Method("DELETE")
     *
     * Deletes multiple groups.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteGroupsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $params = $this->get('request')->query->all();
        $groups = array();

        if (isset($params['ids'])) {
            foreach ($params['ids'] as $groupId) {
                $group = $em->getRepository('ClarolineCoreBundle:Group')
                    ->find($groupId);
                $em->remove($group);
                $groups[] = $group;
            }
        }

        foreach ($groups as $deletedGroup) {
            $log = new LogGroupDeleteEvent($deletedGroup);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }
        $em->flush();

        return new Response('groups removed', 204);
    }

    /**
     * @Route(
     *     "/group/settings/form/{groupId}",
     *     name="claro_admin_group_settings_form",
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * Displays an edition form for a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupSettingsFormAction($groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);
        $form = $this->createForm(new GroupSettingsType(), $group);

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/group/settings/update/{groupId}",
     *     name="claro_admin_update_group_settings"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration:groupSettingsForm.html.twig")
     *
     * Updates the settings of a group and redirects to the group list.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateGroupSettingsAction($groupId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);

        $oldPlatformRoleTransactionKey = $group->getPlatformRole()->getTranslationKey();

        $form = $this->createForm(new GroupSettingsType(), $group);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $group = $form->getData();

            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($group);

            //The changeSet don't manage manyToMany
            $newPlatformRoleTransactionKey = $group->getPlatformRole()->getTranslationKey();
            if ($oldPlatformRoleTransactionKey !== $newPlatformRoleTransactionKey) {
                $changeSet['platformRole'] = array($oldPlatformRoleTransactionKey, $newPlatformRoleTransactionKey);
            }

            $em->persist($group);
            $em->flush();

            $log = new LogGroupUpdateEvent($group, $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/platform/settings/form",
     *     name="claro_admin_platform_settings_form"
     * )
     * @Route(
     *     "/",
     *     name="claro_admin_index",
     *     options={"expose"=true}
     * )
     *
     * @Template()
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function platformSettingsFormAction()
    {
        $platformConfig = $this->get('claroline.config.platform_config_handler')
            ->getPlatformConfig();
        $form = $this->createForm(new PlatformParametersType($this->getThemes()), $platformConfig);

        return array('form_settings' => $form->createView());
    }

    /**
     * @Route(
     *     "claro_admin_update_platform_settings",
     *     name="claro_admin_update_platform_settings"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration:platformSettingsForm.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updatePlatformSettingsAction()
    {
        $request = $this->get('request');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $form = $this->get('form.factory')->create(new PlatformParametersType($this->getThemes()));
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $configHandler->setParameter('allow_self_registration', $form['selfRegistration']->getData());
                $configHandler->setParameter('locale_language', $form['localLanguage']->getData());
                $configHandler->setParameter('theme', $form['theme']->getData());
            } catch (UnwritableException $e) {
                $form->addError(
                    new FormError(
                        $this->get('translator')
                        ->trans('unwritable_file_exception', array('%path%' => $e->getPath()), 'platform')
                    )
                );

                return array('form_settings' => $form->createView());
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_platform_settings_form'));
    }

    /**
     * @Route(
     *     "plugins",
     *     name="claro_admin_plugins"
     * )
     * @Method("GET")
     *
     * @Template()
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
     * @Route(
     *     "/plugin/{domain}/options",
     *     name="claro_admin_plugin_options"
     * )
     * @Method("GET")
     *
     * Redirects to the plugin mangagement page.
     *
     * @param string $domain
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function pluginParametersAction($domain)
    {
        $event = new PluginOptionsEvent();
        $eventName = "plugin_options_{$domain}";
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Custom event '{$eventName}' didn't return any Response."
            );
        }

        return $event->getResponse();
    }

    /**
     * @Route(
     *    "user/management",
     *    name="claro_admin_users_management"
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * @return Response
     */
    public function usersManagementAction()
    {
        return array();
    }

    /**
     * @Route(
     *    "user/management/import/form",
     *     name="claro_admin_import_users_form"
     * )
     * @Method("GET")
     *
     * @Template()
     *
     * @return Response
     */
    public function importUsersFormAction()
    {
        $form = $this->createForm(new ImportUserType());

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "user/management/import",
     *     name="claro_admin_import_users"
     * )
     *
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Administration:importUsersForm.html.twig")
     *
     * @return Response
     */
    public function importUsers()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new ImportUserType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL, ',');

            foreach ($lines as $line) {
                $users[] = str_getcsv($line);
            }

            $this->get('claroline.user.creator')->import($users);

            return $this->redirect($this->generateUrl('claro_admin_users_management'));
        }

        return array('form' => $form->createView());
    }

    /**
     *  Get the list of themes availables.
     *  @TODO use directory iterator
     *
     *  @param $path string The path of the themes.
     *
     *  @return array with a list of the themes availables.
     */
    private function getThemes($path = "/../Resources/views/less/")
    {
        $tmp = array();

        $manager = $this->getDoctrine()->getManager();
        $themes = $manager->getRepository("ClarolineCoreBundle:Theme\Theme")->findAll();

        foreach ($themes as $theme) {
            $tmp[$theme->getPath()] = $theme->getName();
        }

        return $tmp;
    }

    /**
     * @Route(
     *     "/logs/",
     *     name="claro_admin_logs_show",
     *     defaults={"page" = 1}
     * )
     * @Route(
     *     "/logs/{page}",
     *     name="claro_admin_logs_show_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     * @Method("GET")
     *
     * @Template()
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
     * @Route(
     *     "/analytics/",
     *     name="claro_admin_analytics_show"
     * )
     * 
     * @Method("GET")
     *
     * Displays platform analytics home page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $actionsForRange = $this->get('claroline.analytics.manager')->getDailyActionNumberForDateRange();
        $lastMonthActions = $actionsForRange["chartData"];
        $mostViewedWS = $this->get('claroline.analytics.manager')->topWSByAction(null, 'ws_tool_read', 5);
        $mostViewedMedia = $this->get('claroline.analytics.manager')->topMediaByAction(null, 'resource_read', 5);
        $mostDownloadedResources = $this->get('claroline.analytics.manager')->topResourcesByAction(null, 'resource_export', 5);
        $usersCount = $manager->getRepository('ClarolineCoreBundle:User')->count();
        return $this->render(
            'ClarolineCoreBundle:Administration:analytics.html.twig', 
            array(
                'barChartData'=>$lastMonthActions, 
                'usersCount'=>$usersCount,
                'mostViewedWS'=>$mostViewedWS,
                'mostViewedMedia'=>$mostViewedMedia,
                'mostDownloadedResources'=>$mostDownloadedResources
            )
        );
    }

    /**
     * @Route(
     *     "/analytics/connections",
     *     name="claro_admin_analytics_connections"
     * )
     * 
     * @Method({"GET", "POST"})
     *
     * Displays platform analytics connections page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsConnectionsAction()
    {
        $request = $this->get('request');
        $criteria_form = $this->createForm(new AdminAnalyticsConnectionsType());
        $clone_form = clone $criteria_form; 
        $criteria_form->bind($request);
        $unique = false;
        if ($criteria_form->isValid()) {
            $range = $criteria_form->get('range')->getData();
            $unique = ($criteria_form->get('unique')->getData()=='true')?true:false;
        }
        $manager = $this->get('doctrine.orm.entity_manager');
        $actionsForRange = $this
                        ->get('claroline.analytics.manager')
                        ->getDailyActionNumberForDateRange($range, 'user_login',$unique);
        if ($range === null) {
            $clone_form->get('range')->setData($actionsForRange['range']);
            $clone_form->get('unique')->setData($unique);
            $criteria_form = $clone_form;
        }
        
        $connections = $actionsForRange['chartData'];
        $activeUsers = $this->get('claroline.analytics.manager')->getActiveUsers();        

        return $this->render(
            'ClarolineCoreBundle:Administration:analytics_connections.html.twig', 
            array(
                'connections'=>$connections,
                'form_criteria' => $criteria_form->createView(),
                'activeUsers'=>$activeUsers
            )
        );
    }

    /**
     * @Route(
     *     "/analytics/resources",
     *     name="claro_admin_analytics_resources"
     * )
     * 
     * @Method("GET")
     *
     * Displays platform analytics resources page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsResourcesAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $wsCount = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->count();
        $resourceCount = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->countResourcesByType();
        return $this->render(
            'ClarolineCoreBundle:Administration:analytics_resources.html.twig', 
            array(
                'wsCount'=>$wsCount,
                'resourceCount'=>$resourceCount
            )
        );
    }

    /**
     * @Route(
     *     "/analytics/top/{top_type}",
     *     name="claro_admin_analytics_top",
     *     defaults={"top_type" = "top_users_connections"}
     * )
     * 
     * @Method({"GET", "POST"})
     *
     * Displays platform analytics top activity page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsTopAction($top_type)
    {
        $request = $this->get('request');
        $criteria_form = $this->createForm(new AdminAnalyticsTopType());
        $clone_form = clone $criteria_form;
        $criteria_form->bind($request);

        $range = $criteria_form->get('range')->getData();
        if($range===null) {
            $range = $this->get('claroline.analytics.manager')->getDefaultRange();
        }
        $top_type_temp = $criteria_form->get('top_type')->getData();
        $top_type = ($top_type_temp!==null)?$top_type_temp:$top_type;
        $max = $criteria_form->get('top_number')->getData();
        $max = ($max!==null)?intval($max):30;

        $listData = $this
                        ->get('claroline.analytics.manager')
                        ->getTopByCriteria($range, $top_type, $max);
        
        $clone_form->get('range')->setData($range);
        $clone_form->get('top_type')->setData($top_type);
        $clone_form->get('top_number')->setData($max);
        $criteria_form = $clone_form;

        return $this->render(
            'ClarolineCoreBundle:Administration:analytics_top.html.twig', 
            array(
                'form_criteria'=>$criteria_form->createView(),
                'list_data' => $listData
            )
        );
    }
}
