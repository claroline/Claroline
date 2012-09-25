<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Form\GroupSettingsType;
use Claroline\CoreBundle\Form\PlatformParametersType;
use Claroline\CoreBundle\Library\Workspace\Configuration;

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
        $userRoles = $this->get('security.context')->getToken()->getUser()->getOwnedRoles();
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
        $userRoles = $this->get('security.context')->getToken()->getUser()->getOwnedRoles();
        $form = $this->get('form.factory')->create(new ProfileType($userRoles), new User());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $type = Configuration::TYPE_SIMPLE;
            $config = new Configuration();
            $config->setWorkspaceType($type);
            $config->setWorkspaceName($user->getUsername());
            $config->setWorkspaceCode('PERSO');
            $wsCreator = $this->get('claroline.workspace.creator');
            $workspace = $wsCreator->createWorkspace($config, $user);
            $workspace->setType(AbstractWorkspace::USER_REPOSITORY);
            $user->addRole($workspace->getManagerRole());
            $user->setPersonnalWorkspace($workspace);
            $em->persist($workspace);
            $em->flush();

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        return $this->render(
            'ClarolineCoreBundle:Administration:user_creation_form.html.twig',
            array('form_complete_user' => $form->createView())
        );
    }

    /**
     * Deletes an user from the platform.
     *
     * @param integer $userId
     *
     * @throws Exception if the user to be deleted is the current logged user
     *
     * @return type
     */
    public function deleteUserAction($userId)
    {
        if ($userId != $this->get('security.context')->getToken()->getUser()->getId()) {
            $em = $this->getDoctrine()->getEntityManager();
            $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
            $em->remove($user);
            $em->flush();

            return new Response('user removed', 204);
        }

//Doctrine throws an error itself because
//"You cannot refresh a user from the EntityUserProvider that does not contain an identifier.
//The user object has to be serialized with its own identifier mapped by Doctrine. (500 Internal Server Error)
//throw new \Exception('A user cannot delete his own profile.');
    }

    /**
     * Removes many users from the platform.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multiDeleteUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $params = $this->get('request')->query->all();
        unset($params['_']);

        foreach ($params as $userId) {
            $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
            $em->remove($user);
        }

        $em->flush();

        return new Response('user(s) removed', 204);
    }

    /**
     * Displays the platform user list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userListAction()
    {
        return $this->render(
            'ClarolineCoreBundle:Administration:user_list_main.html.twig');
    }

    /**
     * Returns the platform users.
     *
     * @param $offset
     * @param $format
     *
     * @return Response
     */
    public function paginatedUserListAction($offset, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->findPaginatedUsers($offset, self::USER_PER_PAGE, \Claroline\CoreBundle\Repository\UserRepository::PLATEFORM_ROLE);

        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.{$format}.twig", array('users' => $users));

        $response = new Response($content);

        if  ($format == 'json') {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    /**
     * Returns the platform users whose name, username or lastname matche $search.
     *
     * @param type $offset
     * @param type $limit
     * @param type $search
     *
     * @return Response
     */
    public function searchPaginatedUsersAction($offset, $search, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->searchPaginatedUsers($search, $offset, self::USER_PER_PAGE, \Claroline\CoreBundle\Repository\UserRepository::PLATEFORM_ROLE);

        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.{$format}.twig", array('users' => $users));

        $response = new Response($content);

        if  ($format == 'json') {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    // Doesn't work yet due to a sql error from the repository
    public function paginatedUserOfGroupListAction($groupId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->findPaginatedUsersOfGroup($groupId, $offset, self::USER_PER_PAGE);

        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.json.twig", array('users' => $users));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns the platform group list.
     *
     * @param $offset the offset.
     * @param $format the format.
     *
     * @return Response.
     */
    public function paginatedGroupListAction($offset, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $groups = $em->getRepository('Claroline\CoreBundle\Entity\Group')->findPaginatedGroups($offset, self::GROUP_PER_PAGE);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:group_list.{$format}.twig", array('groups' => $groups));
        $response = new Response($content);

        if  ($format == 'json') {
            $response->headers->set('Content-Type', 'application/json');
        }

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
    public function searchPaginatedGroupsAction($offset, $search, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $groups = $em->getRepository('Claroline\CoreBundle\Entity\Group')->searchPaginatedGroups($search, $offset, self::GROUP_PER_PAGE);
        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:group_list.{$format}.twig", array('groups' => $groups));
        $response = new Response($content);

        if ($format == 'json') {
            $response->headers->set('Content-Type', 'application/json');
        }

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
        $pages = ceil($count/self::USER_PER_PAGE);

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
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);

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
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);

        return $this->render(
            'ClarolineCoreBundle:Administration:add_user_to_group_main.html.twig',
            array('group' => $group)
        );
    }

    /**
     * Returns a list of users not registered to the Group $group.
     *
     * @param integer $group
     * @param integer $offset
     *
     * @return Response
     */
    public function paginatedGrouplessUsersAction($groupId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->findUnregisteredUsersFromGroup($groupId, $offset, self::USER_PER_PAGE);

        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.json.twig", array('users' => $users));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a list of users not registered to the Group $group whose username, firstname or lastname
     * matche $search.
     *
     * @param integer search
     * @param integer $group
     * @param integer $offset
     *
     * @return Response
     */
    public function searchPaginatedGrouplessUsersAction($groupId, $search, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->searchUnregisteredUsersFromGroup($groupId, $search, $offset, self::USER_PER_PAGE);

        $content = $this->renderView(
            "ClarolineCoreBundle:Administration:user_list.json.twig", array('users' => $users));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Adds an user to a group and redirects to the group list.
     *
     * @param integer $groupId
     * @param integer $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addUserToGroupAction($groupId, $userId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
        $group->addUser($user);
        $em->persist($group);
        $em->flush();

        return $this->redirect($this->generateUrl('claro_admin_group_list'));
    }

    /**
     * Adds multiple user to a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function multiaddUserstoGroupAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $params = $this->get('request')->query->all();
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
        unset($params['_']);

        foreach ($params as $userId) {
            $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
            if($user !== null){
                $group->addUser($user);
            }
        }

        $em->persist($group);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * Deletes an user from a group and redirects to the group list.
     *
     * @param integer $groupId
     * @param integer $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUserFromGroupAction($groupId, $userId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
        $group->removeUser($user);
        $em->persist($group);
        $em->flush();

        return new Response('user removed', 204);
    }

    /**
     * Deletes a group and redirects to the group list.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteGroupAction($groupId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
        $em->remove($group);
        $em->flush();

        return new Response('group(s) removed', 204);
    }

    /**
     * Deletes multiple groups.
     *
     *  @return Response
     */
    public function multiDeleteGroupAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $params = $this->get('request')->query->all();
        unset($params['_']);

        foreach ($params as $groupId) {
            $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
            $em->remove($group);
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
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
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
        $group = $em->getRepository('Claroline\CoreBundle\Entity\Group')->find($groupId);
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
}