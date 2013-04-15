<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Form\GroupSettingsType;
use Claroline\CoreBundle\Form\PlatformParametersType;
use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Event\LogUserDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupCreateEvent;
use Claroline\CoreBundle\Library\Event\LogGroupAddUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupRemoveUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupUpdateEvent;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Claroline\CoreBundle\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Controller of the platform administration section (users, groups,
 * workspaces, platform settings, etc.).
 */
class AdministrationController extends Controller
{
    const USER_PER_PAGE = 40;
    const GROUP_PER_PAGE = 40;

    /**
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Administration:index.html.twig');
    }

    /**
     * @Route(
     *     "/user/form",
     *     name="claro_admin_user_creation_form"
     * )
     * @Method("GET")
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

        return $this->render(
            'ClarolineCoreBundle:Administration:user_creation_form.html.twig',
            array('form_complete_user' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/user",
     *     name="claro_admin_create_user"
     * )
     * @Method("POST")
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
        $form->bind($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            foreach ($newRoles as $role) {
                $user->addRole($role);
            }
            $this->get('claroline.user.creator')->create($user);

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:user_creation_form.html.twig',
            array('form_complete_user' => $form->createView())
        );
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
     *     "users",
     *     name="claro_admin_user_list"
     * )
     * @Method("GET")
     *
     * Displays the platform user list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userListAction()
    {
        return $this->render('ClarolineCoreBundle:Administration:user_list_main.html.twig');
    }

    /**
     * @Route(
     *     "/users/{offset}",
     *     name="claro_admin_paginated_user_list",
     *     requirements={"offset"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns the platform users.
     *
     * @param $offset
     *
     * @return Response
     */
    public function usersAction($offset)
    {
        $em = $this->getDoctrine()->getManager();
        // TODO: quick fix (force doctrine to reload only the concerned roles
        // -- otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findAll($offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.html.twig",
            array('users' => $users)
        );
        $response = new Response($content);

        return $response;
    }

    /**
     * @Route(
     *     "/users/search/{search}/{offset}",
     *     name="claro_admin_paginated_search_user_list",
     *     requirements={"offset"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns the platform users whose name, username or lastname matche $search.
     *
     * @param integer $offset
     * @param string $search
     *
     * @return Response
     */
    public function searchUsersAction($offset, $search)
    {
        $em = $this->getDoctrine()->getManager();
        // TODO: quick fix (force doctrine to reload only the concerned roles
        // -- otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByName($search, $offset, self::USER_PER_PAGE, UserRepository::PLATEFORM_ROLE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.html.twig",
            array('users' => $users)
        );

        $response = new Response($content);

        return $response;
    }

    /**
     * @Route(
     *     "/group/{groupId}/users/{offset}",
     *     name="claro_admin_paginated_group_user_list",
     *     options={"expose"=true},
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$", "offset"="^(?=.*[0-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Returns the group users.
     *
     * @param integer $groupId
     * @param string $offset
     *
     * @return Response
     */
    // Doesn't work yet due to a sql error from the repository
    public function usersOfGroupAction($groupId, $offset)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByGroup($group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/group/{groupId}/search/{search}/users/{offset}",
     *     name="claro_admin_paginated_search_group_user_list",
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$", "offset"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns the group users whose name or username or lastname matches $search.
     *
     * @param integer $groupId
     * @param integer $offset
     * @param string $search
     *
     * @return Response
     */
    // Doesn't work yet due to a sql error from the repository
    public function searchUsersOfGroupAction($groupId, $offset, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByNameAndGroup($search, $group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/groups/{offset}",
     *     name="claro_admin_paginated_group_list",
     *     options={"expose"=true},
     *     requirements={"offset"="^(?=.*[0-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Returns the platform group list.
     *
     * @param integer $offset the offset.
     *
     * @return Response.
     */
    public function groupsAction($offset)
    {
        $em = $this->getDoctrine()->getManager();
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findAll($offset, self::GROUP_PER_PAGE);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:group_list.html.twig",
            array('groups' => $groups)
        );
        $response = new Response($content);

        return $response;
    }

    /**
     * @Route(
     *     "/groups/search/{search}/{offset}",
     *     name="claro_admin_paginated_search_group_list",
     *     requirements={"offset"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns the platform group list whose names match $search.
     *
     * @param $offset the $offset.
     * @param $search the searched name.
     *
     * @return Response.
     */

    public function searchGroupsAction($offset, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findByName($search, $offset, self::GROUP_PER_PAGE);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:group_list.html.twig",
            array('groups' => $groups)
        );
        $response = new Response($content);

        return $response;
    }

    /**
     * @Route(
     *     "/group/form",
     *     name="claro_admin_group_creation_form"
     * )
     * @Method("GET")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupCreationFormAction()
    {
        $form = $this->createForm(new GroupType(), new Group());

        return $this->render(
            'ClarolineCoreBundle:Administration:group_creation_form.html.twig',
            array('form_group' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/group",
     *     name="claro_admin_create_group"
     * )
     * @Method("POST")
     *
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createGroupAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new GroupType(), new Group());
        $form->bind($request);

        if ($form->isValid()) {
            $group = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            $log = new LogGroupCreateEvent($group);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:group_creation_form.html.twig',
            array('form_group' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/groups",
     *     name="claro_admin_group_list"
     * )
     * @Method("GET")
     *
     * Displays the platform group list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupListAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT COUNT(g.id) FROM Claroline\CoreBundle\Entity\Group g');
        $count = $query->getSingleScalarResult();
        $pages = ceil($count / self::USER_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_list_main.html.twig',
            array('pages' => $pages)
        );
    }

    /**
     * @Route(
     *     "/group/{groupId}",
     *     name="claro_admin_group_user_list",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Displays the users of a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupUserListAction($groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_user_list_main.html.twig',
            array('group' => $group)
        );
    }

    /**
     * @Route(
     *     "/group/add/{groupId}",
     *     name="claro_admin_user_list_addable_to_group",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Displays the user list with a control allowing to add them to a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUserToGroupLayoutAction($groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);

        return $this->render(
            'ClarolineCoreBundle:Administration:add_user_to_group_main.html.twig',
            array('group' => $group)
        );
    }

    /**
     * @Route(
     *     "/group/{groupId}/unregistered/users/{offset}",
     *     name="claro_admin_groupless_users",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$", "offset"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns a list of users not registered to the Group $group.
     *
     * @param integer $groupId
     * @param integer $offset
     *
     * @return Response
     */
    public function grouplessUsersAction($groupId, $offset)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findGroupOutsiders($group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/group/{groupId}/unregistered/users/{offset}/search/{search}",
     *     name="claro_admin_search_groupless_users",
     *     requirements={"offset"="^(?=.*[0-9].*$)\d*$", "groupId"="^(?=.*[1-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns a list of users not registered to the Group $group whose username, firstname
     * or lastname matches $search.
     *
     * @param integer search
     * @param integer $group
     * @param integer $offset
     *
     * @return Response
     */
    public function searchGrouplessUsersAction($groupId, $search, $offset)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findGroupOutsidersByName($group, $search, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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

        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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

        if (isset($params['ids'])) {
            foreach ($params['ids'] as $groupId) {
                $group = $em->getRepository('ClarolineCoreBundle:Group')
                    ->find($groupId);
                $em->remove($group);
            }
        }

        $em->flush();

        $log = new LogGroupDeleteEvent($group);
        $this->get('event_dispatcher')->dispatch('log', $log);

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

        return $this->render(
            'ClarolineCoreBundle:Administration:group_settings_form.html.twig',
            array('group' => $group, 'form_settings' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/group/settings/update/{groupId}",
     *     name="claro_admin_update_group_settings"
     * )
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
        $form->bind($request);

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

        return $this->render(
            'ClarolineCoreBundle:Administration:group_settings_form.html.twig',
            array('group' => $group, 'form_settings' => $form->createView())
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
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function platformSettingsFormAction()
    {
        $platformConfig = $this->get('claroline.config.platform_config_handler')
            ->getPlatformConfig();
        $form = $this->createForm(new PlatformParametersType($this->getThemes()), $platformConfig);

        return $this->render(
            'ClarolineCoreBundle:Administration:platform_settings_form.html.twig',
            array('form_settings' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "claro_admin_update_platform_settings",
     *     name="claro_admin_update_platform_settings"
     * )
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
        $form->bind($request);

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

                return $this->render(
                    'ClarolineCoreBundle:Administration:platform_settings_form.html.twig',
                    array('form_settings' => $form->createView())
                );
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
     * Display the plugin list
     *
     * @return Response
     */
    public function pluginListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $plugins = $em->getRepository('ClarolineCoreBundle:Plugin')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Administration:plugins.html.twig',
            array('plugins' => $plugins)
        );
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

    private function paginatorToArray($paginator)
    {
        return $this->get('claroline.utilities.paginator_parser')
            ->paginatorToArray($paginator);
    }

    /**
     *  Get the list of themes availables.
     *  @TODO use directory iterator
     *
     *  @param $path string The path of the themes.
     *
     *  @return array with a list of the themes availables.
     */
    private function getThemes($path = "/../Resources/less/themes/")
    {
        $themes = array();

        if ($handle = opendir(__DIR__.$path)) {
            while (false !== ($entry = readdir($handle))) {
                if (strpos($entry, ".") !== 0) {
                    $themes[$entry] = "$entry";
                }
            }

            closedir($handle);
        }

        return $themes;
    }
}
