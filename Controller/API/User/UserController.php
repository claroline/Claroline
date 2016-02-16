<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\User;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileCreationType;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @NamePrefix("api_")
 */
class UserController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager"),
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "apiManager"             = @DI\Inject("claroline.manager.api_manager")
     * })
     */
    public function __construct(
        AuthenticationManager $authenticationManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        Request $request,
        UserManager $userManager,
        GroupManager $groupManager,
        RoleManager $roleManager,
        ObjectManager $om,
        FacetManager $facetManager,
        ProfilePropertyManager $profilePropertyManager,
        MailManager $mailManager,
        ApiManager $apiManager
    )
    {
        $this->authenticationManager  = $authenticationManager;
        $this->eventDispatcher        = $eventDispatcher;
        $this->formFactory            = $formFactory;
        $this->localeManager          = $localeManager;
        $this->request                = $request;
        $this->userManager            = $userManager;
        $this->facetManager           = $facetManager;
        $this->groupManager           = $groupManager;
        $this->roleManager            = $roleManager;
        $this->om                     = $om;
        $this->userRepo               = $om->getRepository('ClarolineCoreBundle:User');
        $this->roleRepo               = $om->getRepository('ClarolineCoreBundle:Role');
        $this->groupRepo              = $om->getRepository('ClarolineCoreBundle:Group');
        $this->profilePropertyManager = $profilePropertyManager;
        $this->mailManager            = $mailManager;
        $this->apiManager             = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the users list",
     *     views = {"user"}
     * )
     */
    public function getUsersAction()
    {
        return $this->userManager->getAll();
    }

    /**
     * @View(serializerGroups={"admin"})
     * @ApiDoc(
     *     description="Returns the users list",
     *     views = {"user"}
     * )
     */
    public function getSearchUsersAction($page, $limit)
    {
        $data = [];
        $searches = $this->request->query->all();
        //format search
        foreach ($searches as $key => $search) {
            switch ($key) {
                case 'first_name': $data['firstName'] = $search; break;
                case 'last_name': $data['lastName'] = $search; break;
                case 'administrative_code': $data['administrativeCode'] = $search; break;
                case 'email': $data['mail'] = $search; break;
                default: $data[$key] = $search;
            }
        }

        $users = $this->userManager->searchPartialList($data, $page, $limit);
        $count = $this->userManager->searchPartialList($data, $page, $limit, true);

        return array('users' => $users, 'total' => $count);
    }

    /**
     * @ApiDoc(
     *     description="Returns the searchable user fields",
     *     views = {"user"}
     * )
     */
    public function getUserSearchableFieldsAction()
    {
        $fields = $this->facetManager->getFieldFacets();

        $baseFields = User::getSearchableFields();

        foreach ($fields as $field) {
            $baseFields[] = $field->getName();
        }

        $baseFields[] = 'group_name';
        $baseFields[] = 'organization_name';

        return $baseFields;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Creates a user",
     *     views = {"user"},
     *     input="Claroline\CoreBundle\Form\ProfileCreationType"
     * )
     */
    public function postUserAction()
    {
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');

        $profileType = new ProfileCreationType(
            $this->localeManager,
            array($roleUser),
            true,
            $this->authenticationManager->getDrivers()
        );
        $profileType->enableApi();

        $form = $this->formFactory->create($profileType);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $roles = $form->get('platformRoles')->getData();
            $user = $form->getData();
            $user = $this->userManager->createUser($user, false, $roles);
            //maybe only do this if a parameter is present in platform_options.yml
            $this->mailManager->sendInitPassword($user);

            return $user;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update a user",
     *     views = {"user"},
     *     input="Claroline\CoreBundle\Form\ProfileType"
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function putUserAction(User $user)
    {
        $roles = $this->roleManager->getPlatformRoles($user);
        $accesses = $this->profilePropertyManager->getAccessessByRoles(array('ROLE_ADMIN'));

        $formType = new ProfileType(
            $this->localeManager,
            $roles,
            true,
            true,
            $accesses,
            $this->authenticationManager->getDrivers()
        );

        $formType->enableApi();
        $userRole = $this->roleManager->getUserRoleByUser($user);
        $form = $this->formFactory->create($formType, $user);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->roleManager->renameUserRole($userRole, $user->getUsername());
            $this->userManager->rename($user, $user->getUsername());

            if (isset($form['platformRoles'])) {
                //verification:
                //only the admin can grant the role admin
                //simple users cannot change anything. Don't let them put whatever they want with a fake form.
                $newRoles = $form['platformRoles']->getData();
                $this->userManager->setPlatformRoles($user, $newRoles);
            }

            return $user;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns a user",
     *     views = {"user"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a user",
     *     section="user",
     *     views = {"api"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function deleteUserAction(User $user)
    {
        $this->userManager->deleteUser($user);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a list of users",
     *     views = {"group"},
     * )
     */
    public function deleteUsersAction()
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->container->get('claroline.persistence.object_manager')->startFlushSuite();

        foreach ($users as $user) {
            $this->userManager->deleteUser($user);
        }

        $this->container->get('claroline.persistence.object_manager')->endFlushSuite();

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Add a role to a user",
     *     views = {"user"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserRoleAction(User $user, Role $role)
    {
        $this->roleManager->associateRole($user, $role, false);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="remove a role from a user",
     *     views = {"user"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function removeUserRoleAction(User $user, Role $role)
    {
        $this->roleManager->dissociateRole($user, $role);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Add a user in a group",
     *     views = {"user"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserGroupAction(User $user, Group $group)
    {
        $this->groupManager->addUsersToGroup($group, array($user));

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Remove a user from a group",
     *     views = {"user"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function removeUserGroupAction(User $user, Group $group)
    {
        $this->groupManager->removeUsersFromGroup($group, array($user));

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Returns the list of actions an admin can do on a user",
     *     views = {"user"}
     * )
     */
    public function getUserAdminActionsAction()
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\UserAdminAction')->findAll();
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Send the password initialization message for a user.",
     *     views = {"user"}
     * )
     */
    public function usersPasswordInitializeAction()
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');

        foreach ($users as $user) {
            $this->mailManager->sendForgotPassword($user);
        }

        return array('success');
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Add a list of users to a group",
     *     views = {"group"},
     * )
     */
    public function addUsersToGroupAction(Group $group)
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $users = $this->groupManager->addUsersToGroup($group, $users);

        return $users;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Removes a list of users from a group",
     *     views = {"group"},
     * )
     */
    public function removeUsersFromGroupAction(Group $group)
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->groupManager->removeUsersFromGroup($group, $users);

        return $users;
    }

    /**
     *
     */
    public function importUsersAction()
    {

    }
}
