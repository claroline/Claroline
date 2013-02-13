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
use Claroline\CoreBundle\Library\Plugin\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Repository\UserRepository;

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
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction()
    {
        $userRoles = $this->get('security.context')
            ->getToken()
            ->getUser()
            ->getOwnedRoles();
        $form = $this->createForm(new ProfileType($userRoles));

        return $this->render(
            'ClarolineCoreBundle:Administration:user_creation_form.html.twig',
            array('form_complete_user' => $form->createView())
        );
    }

    /**
     * Creates an user (and its personal workspace) and redirects to the user list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createUserAction()
    {
        $request = $this->get('request');
        $userRoles = $this->get('security.context')
            ->getToken()
            ->getUser()
            ->getOwnedRoles();
        $form = $this->get('form.factory')->create(new ProfileType($userRoles), new User());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->get('claroline.user.creator')->create($user);

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:user_creation_form.html.twig',
            array('form_complete_user' => $form->createView())
        );
    }

    /**
     * Removes many users from the platform.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUsersAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->getDoctrine()->getEntityManager();

            foreach ($params['ids'] as $userId) {
                $user = $em->getRepository('ClarolineCoreBundle:User')
                    ->find($userId);
                $em->remove($user);
            }

            $em->flush();
        }

        return new Response('user(s) removed', 204);
    }

    /**
     * Displays the platform user list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userListAction()
    {
        return $this->render('ClarolineCoreBundle:Administration:user_list_main.html.twig');
    }

    /**
     * Returns the platform users.
     *
     * @param $offset
     *
     * @return Response
     */
    public function usersAction($offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
     * Returns the platform users whose name, username or lastname matche $search.
     *
     * @param integer $offset
     * @param string $search
     *
     * @return Response
     */
    public function searchUsersAction($offset, $search)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByGroup($group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
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
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByNameAndGroup($search, $group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns the platform group list.
     *
     * @param integer $offset the offset.
     *
     * @return Response.
     */
    public function groupsAction($offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
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

    /*
     * Returns the platform group list whose names match $search.
     *
     * @param $offset the $offset.
     * @param $search the searched name.
     *
     * @return Response.
     */

    public function searchGroupsAction($offset, $search)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createGroupAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new GroupType(), new Group());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $group = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($group);
            $em->flush();

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:group_creation_form.html.twig',
            array('form_group' => $form->createView())
        );
    }

    /**
     * Displays the platform group list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupListAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(g.id) FROM Claroline\CoreBundle\Entity\Group g');
        $count = $query->getSingleScalarResult();
        $pages = ceil($count / self::USER_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_list_main.html.twig',
            array('pages' => $pages)
        );
    }

    /**
     * Displays the users of a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupUserListAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_user_list_main.html.twig',
            array('group' => $group)
        );
    }

    /**
     * Displays the user list with a control allowing to add them to a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUserToGroupLayoutAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);

        return $this->render(
            'ClarolineCoreBundle:Administration:add_user_to_group_main.html.twig',
            array('group' => $group)
        );
    }

    /**
     * Returns a list of users not registered to the Group $group.
     *
     * @param integer $groupId
     * @param integer $offset
     *
     * @return Response
     */
    public function grouplessUsersAction($groupId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findGroupOutsiders($group, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
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
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->find('ClarolineCoreBundle:Group', $groupId);
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->findGroupOutsidersByName($group, $search, $offset, self::USER_PER_PAGE);
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Adds multiple user to a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function addUsersToGroupAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
        $content = $this->renderView(
            'ClarolineCoreBundle:model:users.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Removes users from a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function deleteUsersFromGroupAction($groupId)
    {
        $params = $this->get('request')->query->all();
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);

        if (isset($params['userIds'])) {
            foreach ($params['userIds'] as $userId) {
                $user = $em->getRepository('ClarolineCoreBundle:User')
                    ->find($userId);
                $group->removeUser($user);
                $em->persist($group);
            }
        }

        $em->flush();

        return new Response('user removed', 204);
    }

    /**
     * Deletes multiple groups.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteGroupsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            foreach ($params['ids'] as $groupId) {
                $group = $em->getRepository('ClarolineCoreBundle:Group')
                    ->find($groupId);
                $em->remove($group);
            }
        }

        $em->flush();

        return new Response('groups removed', 204);
    }

    /**
     * Displays an edition form for a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupSettingsFormAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);
        $form = $this->createForm(new GroupSettingsType(), $group);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_settings_form.html.twig',
            array('group' => $group, 'form_settings' => $form->createView())
        );
    }

    /**
     * Updates the settings of a group and redirects to the group list.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateGroupSettingsAction($groupId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);
        $form = $this->createForm(new GroupSettingsType(), $group);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $group = $form->getData();
            $em->persist($group);
            $em->flush();

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:group_settings_form.html.twig',
            array('group' => $group, 'form_settings' => $form->createView())
        );
    }

    /**
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function platformSettingsFormAction()
    {
        $platformConfig = $this->get('claroline.config.platform_config_handler')
            ->getPlatformConfig();
        $form = $this->createForm(new PlatformParametersType(), $platformConfig);

        return $this->render(
            'ClarolineCoreBundle:Administration:platform_settings_form.html.twig',
            array('form_settings' => $form->createView())
        );
    }

    /**
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updatePlatformSettingsAction()
    {
        $request = $this->get('request');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $form = $this->get('form.factory')->create(new PlatformParametersType());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $configHandler->setParameter('allow_self_registration', $form['selfRegistration']->getData());
            $configHandler->setParameter('locale_language', $form['localLanguage']->getData());
            $configHandler->setParameter('theme', $form['theme']->getData());
        }

        //this form can't be invalid
        return $this->redirect($this->generateUrl('claro_admin_platform_settings_form'));
    }

    /**
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
     * Redirects to the plugin mangagement page.
     *
     * @param string $domain
     * @return Response
     * @throws \Exception
     */
    public function pluginParametersAction($domain)
    {
        $event = new PluginOptionsEvent();
        $eventName = strtolower("plugin_options_{$domain}");
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
}