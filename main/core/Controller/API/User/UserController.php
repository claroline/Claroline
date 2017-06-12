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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileCreationType;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Library\Security\Collection\UserCollection;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("api_")
 */
class UserController extends FOSRestController
{
    private $authenticationManager;
    private $eventDispatcher;
    private $formFactory;
    private $localeManager;
    private $request;
    private $userManager;
    private $groupManager;
    private $roleManager;
    private $workspaceManager;
    private $om;
    private $userRepo;
    private $roleRepo;
    private $groupRepo;
    private $profilePropertyManager;
    private $mailManager;
    private $apiManager;
    private $facetManager;

    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "localeManager"          = @DI\Inject("claroline.manager.locale_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "apiManager"             = @DI\Inject("claroline.manager.api_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param AuthenticationManager  $authenticationManager
     * @param StrictDispatcher       $eventDispatcher
     * @param FormFactory            $formFactory
     * @param LocaleManager          $localeManager
     * @param Request                $request
     * @param UserManager            $userManager
     * @param GroupManager           $groupManager
     * @param RoleManager            $roleManager
     * @param FacetManager           $facetManager
     * @param ObjectManager          $om
     * @param ProfilePropertyManager $profilePropertyManager
     * @param MailManager            $mailManager
     * @param ApiManager             $apiManager
     * @param WorkspaceManager       $workspaceManager
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
        FacetManager $facetManager,
        ObjectManager $om,
        ProfilePropertyManager $profilePropertyManager,
        MailManager $mailManager,
        ApiManager $apiManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->localeManager = $localeManager;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->profilePropertyManager = $profilePropertyManager;
        $this->mailManager = $mailManager;
        $this->apiManager = $apiManager;
        $this->facetManager = $facetManager;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Get("/users", name="users", options={ "method_prefix" = false })
     */
    public function getUsersAction()
    {
        $this->throwsExceptionIfNotAdmin();

        return $this->userManager->getAll();
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Get("/users/page/{page}/limit/{limit}/search", name="get_search_users", options={ "method_prefix" = false })
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

        return ['users' => $users, 'total' => $count];
    }

    /**
     * @Get("/users/fields", name="get_user_fields", options={ "method_prefix" = false })
     */
    public function getUserFieldsAction()
    {
        return $this->userManager->getUserSearchableFields();
    }

    /**
     * @View(serializerGroups={"api_user"})
     */
    public function postUserAction()
    {
        $this->throwExceptionIfNotGranted('create', new User());
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');

        $profileType = new ProfileCreationType(
            $this->localeManager,
            [$roleUser],
            $this->container->get('security.token_storage')->getToken()->getUser(),
            $this->authenticationManager->getDrivers()
        );
        $profileType->enableApi();

        $form = $this->formFactory->create($profileType);
        $form->submit($this->request);

        if ($form->isValid()) {
            //can we create the user in the current organization ?

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
     * @View(serializerGroups={"api_user"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     *
     * @param User $user
     *
     * @return User|FormInterface
     */
    public function putUserAction(User $user)
    {
        $this->throwExceptionIfNotGranted('edit', $user);
        $roles = $this->roleManager->getPlatformRoles($user);
        $accesses = $this->profilePropertyManager->getAccessessByRoles(['ROLE_ADMIN']);

        $formType = new ProfileType(
            $this->localeManager,
            $roles,
            true,
            true,
            $accesses,
            $this->authenticationManager->getDrivers(),
            $this->container->get('security.token_storage')->getToken()->getUser()
        );

        // keep track of the previous username before submittingthe form
        $previousUsername = $user->getUsername();

        $formType->enableApi();
        $form = $this->formFactory->create($formType, $user);

        $form->submit($this->request);
        if ($form->isValid()) {
            $user = $form->getData();
            $this->userManager->rename($user, $previousUsername);

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
     * @View(serializerGroups={"api_user"})
     * @Get("/user/{search}/get", name="get_user", options={ "method_prefix" = false })
     */
    public function getUserAction($search)
    {
        $user = $this->userRepo->loadUserByUsername($search);

        return $user;
    }

    /**
     * @Get("/user/{user}/public", name="get_public_user", options={ "method_prefix" = false })
     */
    public function getPublicUserAction(User $user)
    {
        $settingsProfile = $this->facetManager->getVisiblePublicPreference();
        $publicUser = [];

        foreach ($settingsProfile as $property => $isViewable) {
            if ($isViewable || $user === $this->container->get('security.token_storage')->getToken()->getUser()) {
                switch ($property) {
                    case 'baseData':
                        $publicUser['lastName'] = $user->getLastName();
                        $publicUser['firstName'] = $user->getFirstName();
                        $publicUser['username'] = $user->getUsername();
                        $publicUser['picture'] = $user->getPicture();
                        $publicUser['description'] = $user->getAdministrativeCode();
                        break;
                    case 'email':
                        $publicUser['mail'] = $user->getMail();
                        break;
                    case 'phone':
                        $publicUser['phone'] = $user->getPhone();
                        break;
                    case 'sendMail':
                        $publicUser['mail'] = $user->getMail();
                        $publicUser['allowSendMail'] = true;
                        break;
                    case 'sendMessage':
                        $publicUser['allowSendMessage'] = true;
                        $publicUser['id'] = $user->getId();
                        break;
                }
            }
        }

        return $publicUser;
    }

    /**
     * @View()
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function deleteUserAction(User $user)
    {
        $this->throwExceptionIfNotGranted('delete', $user);
        $this->userManager->deleteUser($user);

        return ['success'];
    }

    /**
     * @View()
     */
    public function deleteUsersAction()
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->throwExceptionIfNotGranted('delete', $users);
        $this->container->get('claroline.persistence.object_manager')->startFlushSuite();

        foreach ($users as $user) {
            $this->userManager->deleteUser($user);
        }

        $this->container->get('claroline.persistence.object_manager')->endFlushSuite();

        return ['success'];
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserRoleAction(User $user, Role $role)
    {
        $this->throwExceptionIfNotGranted('edit', $user);
        $this->roleManager->associateRole($user, $role, false);

        return $user;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Put("/users/roles/add", name="put_users_roles", options={ "method_prefix" = false })
     */
    public function putRolesToUsersAction()
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $roles = $this->apiManager->getParameters('roleIds', 'Claroline\CoreBundle\Entity\Role');

        //later make a voter on a user list
        $this->throwsExceptionIfNotAdmin();
        $this->roleManager->associateRolesToSubjects($users, $roles);

        return $users;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function removeUserRoleAction(User $user, Role $role)
    {
        $this->throwExceptionIfNotGranted('edit', $user);
        $this->roleManager->dissociateRole($user, $role);

        return $user;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserGroupAction(User $user, Group $group)
    {
        $this->throwExceptionIfNotGranted('edit', $user);
        $this->groupManager->addUsersToGroup($group, [$user]);

        return $user;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function removeUserGroupAction(User $user, Group $group)
    {
        $this->throwExceptionIfNotGranted('edit', $user);
        $this->groupManager->removeUsersFromGroup($group, [$user]);

        return $user;
    }

    /**
     * @View()
     * @Get("/user/admin/action", name="get_user_admin_actions", options={ "method_prefix" = false })
     */
    public function getUserAdminActionsAction()
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Action\AdditionalAction')->findByType('admin_user_action');
    }

    /**
     * @View()
     */
    public function usersPasswordInitializeAction()
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->throwExceptionIfNotGranted('edit', $users);

        foreach ($users as $user) {
            $this->mailManager->sendForgotPassword($user);
        }

        return ['success'];
    }

    /**
     * @View(serializerGroups={"api_user"})
     */
    public function addUsersToGroupAction(Group $group)
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->throwExceptionIfNotGranted('edit', $users);
        $users = $this->groupManager->addUsersToGroup($group, $users);

        return $users;
    }

    /**
     * @View(serializerGroups={"api_user"})
     */
    public function removeUsersFromGroupAction(Group $group)
    {
        $users = $this->apiManager->getParameters('userIds', 'Claroline\CoreBundle\Entity\User');
        $this->throwExceptionIfNotGranted('edit', $users);
        $this->groupManager->removeUsersFromGroup($group, $users);

        return $users;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Post("/users/csv/remove")
     */
    public function csvRemoveUserAction()
    {
        $this->throwsExceptionIfNotAdmin();

        $this->userManager->csvRemove($this->request->files->get('csv'));
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Post("/user/{user}/disable", name="disable_user", options={ "method_prefix" = false })
     */
    public function disableUserAction(User $user)
    {
        $this->throwsExceptionIfNotAdmin();

        return $this->userManager->disable($user);
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Post("/user/{user}/enable", name="enable_user", options={ "method_prefix" = false })
     */
    public function enableUserAction(User $user)
    {
        $this->throwsExceptionIfNotAdmin();

        return $this->userManager->enable($user);
    }

     /**
      * @View(serializerGroups={"api_user"})
      * @Post("/users/csv/facets")
      */
     public function csvImportFacetsAction()
     {
         $this->throwsExceptionIfNotAdmin();

         $this->userManager->csvFacets($this->request->files->get('csv'));
     }

    private function isAdmin()
    {
        return $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
    }

    private function throwsExceptionIfNotAdmin()
    {
        if (!$this->isAdmin()) {
            throw new AccessDeniedException('This action can only be done by the administrator');
        }
    }

    private function isUserGranted($action, $object)
    {
        return $this->container->get('security.authorization_checker')->isGranted($action, $object);
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Post("/pws/create/{user}")
     */
    public function createPersonalWorkspaceAction(User $user)
    {
        if (!$user->getPersonalWorkspace()) {
            $this->userManager->setPersonalWorkspace($user);
        } else {
            throw new \Exception('Workspace already exists');
        }

        return $user;
    }

    /**
     * @View(serializerGroups={"api_user"})
     * @Post("/pws/delete/{user}")
     */
    public function deletePersonalWorkspaceAction(User $user)
    {
        $personalWorkspace = $user->getPersonalWorkspace();
        $this->eventDispatcher->dispatch('log', 'Log\LogWorkspaceDelete', [$personalWorkspace]);
        $this->workspaceManager->deleteWorkspace($personalWorkspace);

        return $user;
    }

    private function throwExceptionIfNotGranted($action, $users)
    {
        $collection = is_array($users) ? new UserCollection($users) : new UserCollection([$users]);
        $isGranted = $this->isUserGranted($action, $collection);

        if (!$isGranted) {
            $userlist = '';

            foreach ($collection->getUsers() as $user) {
                $userlist .= "[{$user->getUsername()}]";
            }
            throw new AccessDeniedException("You can't do the action [{$action}] on the user list {$userlist}");
        }
    }
}
