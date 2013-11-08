<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\Mapping as ORM;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    /** @var UserRepository */
    private $userRepo;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $ed;
    private $personalWsTemplateFile;
    private $translator;
    private $ch;
    private $pagerFactory;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "ed"                     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "translator"             = @DI\Inject("translator"),
     *     "ch"                     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "pagerFactory"           = @DI\Inject("claroline.pager.pager_factory"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager,
        StrictDispatcher $ed,
        $personalWsTemplateFile,
        Translator $translator,
        PlatformConfigurationHandler $ch,
        PagerFactory $pagerFactory,
        ObjectManager $om
    )
    {
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->translator = $translator;
        $this->ch = $ch;
        $this->pagerFactory = $pagerFactory;
        $this->om = $om;
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUser(User $user)
    {
        $this->om->startFlushSuite();
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, PlatformRoles::USER);
        $this->om->persist($user);
        $this->ed->dispatch('log', 'Log\LogUserCreate', array($user));
        $this->om->endFlushSuite();

        return $user;
    }

    /**
     * Removes a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function deleteUser(User $user)
    {
        $this->om->remove($user);
        $this->om->flush();
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     * This user will have the additional role  $roleName.
     * $roleName must already exists.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string $roleName
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUserWithRole(User $user, $roleName)
    {
        $this->om->startFlushSuite();
        $this->createUser($user);
        $this->roleManager->setRoleToRoleSubject($user, $roleName);
        $this->om->endFlushSuite();

        return $user;
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     * This user will have the additional roles $roles.
     * These roles must already exists.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Doctrine\Common\Collections\ArrayCollection $roles
     */
    public function insertUserWithRoles(User $user, ArrayCollection $roles)
    {
        $this->om->startFlushSuite();
        $this->createUser($user);
        $this->roleManager->associateRoles($user, $roles);
        $this->om->endFlushSuite();
    }

    /**
     * Import users from an array.
     * There is the array format:
     *
     * array(
     *     array(firstname, lastname, username, pwd, email, code, phone),
     *     array(firstname2, lastname2, username2, pwd2, email2, code2, phone2),
     *     array(firstname3, lastname3, username3, pwd3, email3, code3, phone3),
     * )
     *
     * @param array $users
     *
     * @return array
     */
    public function importUsers(array $users)
    {
        $nonImportedUsers = array();
        $this->om->startFlushSuite();

        //batch processing. Require a counter to flush every ~150 users.
        foreach ($users as $user) {
            $firstName = $user[0];
            $lastName = $user[1];
            $username = $user[2];
            $pwd = $user[3];
            $email = $user[4];
            $code = isset($user[5])? $user[5] : null;
            $phone = isset($user[6])? $user[6] : null;
            $existingUsers = $this->userRepo->findUserByUsernameOrEmail($username, $email);

            if (count($existingUsers) === 0 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $newUser = $this->om->factory('Claroline\CoreBundle\Entity\User');
                $newUser->setFirstName($firstName);
                $newUser->setLastName($lastName);
                $newUser->setUsername($username);
                $newUser->setPlainPassword($pwd);
                $newUser->setMail($email);
                $newUser->setAdministrativeCode($code);
                $newUser->setPhone($phone);
                $this->createUser($newUser);
            } else {
                $nonImportedUsers[] = array(
                    'username' => $username,
                    'firstName' => $firstName,
                    'lastName' => $lastName
                );
            }
        }

        $this->om->endFlushSuite();

        return $nonImportedUsers;
    }

    /**
     * Creates the personal workspace of a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function setPersonalWorkspace(User $user)
    {
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->ch->getParameter('locale_language');
        $this->translator->setLocale($locale);
        $personalWorkspaceName = $this->translator->trans('personal_workspace', array(), 'platform');
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());

        $workspace = $this->workspaceManager->create($config, $user);
        $user->setPersonalWorkspace($workspace);
        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * Serialize a user.
     *
     * @param array $users
     *
     * @return array
     */
    public function convertUsersToArray(array $users)
    {
        $content = array();
        $i = 0;

        foreach ($users as $user) {
            $content[$i]['id'] = $user->getId();
            $content[$i]['username'] = $user->getUsername();
            $content[$i]['lastname'] = $user->getLastName();
            $content[$i]['firstname'] = $user->getFirstName();
            $content[$i]['administrativeCode'] = $user->getAdministrativeCode();

            $rolesString = '';
            $roles = $user->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;

            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), array(), 'platform')}";

                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                $j++;
            }
            $content[$i]['roles'] = $rolesString;
            $i++;
        }

        return $content;
    }

    /**
     * @param type $username
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        return $this->userRepo->loadUserByUsername($username);
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @return User[]
     */
    public function getUserByWorkspaceAndRole(AbstractWorkspace $workspace, Role $role)
    {
        return $this->userRepo->findByWorkspaceAndRole($workspace, $role);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string $search
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $page, $max = 20)
    {
        $query = $this->userRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsers($page, $max = 20)
    {
        $query = $this->userRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string $search
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsersBySearch($page, $search, $max = 20)
    {
        $users = $this->userRepo->findAllUserBySearch($search);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    /**
     * @param string $search
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByName($search, $page, $max = 20)
    {
        $query = $this->userRepo->findByName($search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByGroup(Group $group, $page, $max = 20)
    {
        $query = $this->userRepo->findByGroup($group, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     *
     * @return User[]
     */
    public function getUsersByGroupWithoutPager(Group $group)
    {
        return $this->userRepo->findByGroup($group);
    }

    /**
     *
     * @param string $search
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByNameAndGroup($search, Group $group, $page, $max = 20)
    {
        $query = $this->userRepo->findByNameAndGroup($search, $group, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspace(AbstractWorkspace $workspace, $page, $max = 20)
    {
        $query = $this->userRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspaces(array $workspaces, $page, $max = 20)
    {
        $query = $this->userRepo->findUsersByWorkspaces($workspaces, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     * @param integer $page
     * @param string $search
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspacesAndSearch(
        array $workspaces,
        $page,
        $search,
        $max = 20
    )
    {
        $users = $this->userRepo
            ->findUsersByWorkspacesAndSearch($workspaces, $search);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string $search
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findByWorkspaceAndName($workspace, $search, false, $max);

        return $this->pagerFactory->createPager($query, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsiders(Group $group, $page, $max = 20)
    {
        $query = $this->userRepo->findGroupOutsiders($group, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer $page
     * @param string $search
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsidersByName(Group $group, $page, $search, $max = 20)
    {
        $query = $this->userRepo->findGroupOutsidersByName($group, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $excludedUser
     *
     * @return User[]
     */
    public function getAllUsersExcept(User $excludedUser)
    {
        return $this->userRepo->findAllExcept($excludedUser);
    }

    /**
     * @param string[] $usernames
     *
     * @return User[]
     */
    public function getUsersByUsernames(array $usernames)
    {
        return $this->userRepo->findByUsernames($usernames);
    }

    /**
     * @return integer
     */
    public function getNbUsers()
    {
        return $this->userRepo->count();
    }

    /**
     * @param integer[] $ids
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids)
    {
        return $this->om->findByIds('Claroline\CoreBundle\Entity\User', $ids);
    }

    /**
     * @param integer $max
     *
     * @return User[]
     */
    public function getUsersEnrolledInMostWorkspaces($max)
    {
        return $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
    }

    /**
     * @param integer $max
     *
     * @return User[]
     */
    public function getUsersOwnersOfMostWorkspaces($max)
    {
        return $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
    }

    /**
     * @param integer $userId
     *
     * @return User
     */
    public function getUserById($userId)
    {
        return $this->userRepo->find($userId);
    }

    /**
     * @param Role[] $roles
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesIncludingGroups(array $roles, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findByRolesIncludingGroups($roles, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param string $search
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesAndNameIncludingGroups(array $roles, $search, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findByRolesAndNameIncludingGroups($roles, $search, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRoles(array $roles, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findByRoles($roles, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getOutsidersByWorkspaceRoles(array $roles, AbstractWorkspace $workspace, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findOutsidersByWorkspaceRoles($roles, $workspace, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param string $name
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRolesAndName(array $roles, $name, $page = 1, $max  = 20)
    {
        $res = $this->userRepo->findByRolesAndName($roles, $name, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param string $name
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta| \Doctrine\ORM\Query
     */
    public function getOutsidersByWorkspaceRolesAndName(array $roles, $name, AbstractWorkspace $workspace, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findOutsidersByWorkspaceRolesAndName($roles, $name, $workspace, true);

        return ($page !== 0) ? $this->pagerFactory->createPager($res, $page, $max): $res;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail($email)
    {
        return $this->userRepo->findOneByMail($email);
    }

    /**
     * @todo Please describe me. I couldn't find findOneByResetPasswordHash.
     *
     * @param string $resetPassword
     *
     * @return User
     */
    public function getResetPasswordHash($resetPassword)
    {
        return $this->userRepo->findOneByResetPasswordHash($resetPassword);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    function uploadAvatar(User $user)
    {
        if (null !== $user->getPictureFile()) {
            $user->setPicture(sha1($user->getPictureFile()->getClientOriginalName()).'.'.$user->getPictureFile()->guessExtension());
            $user->getPictureFile()->move(__DIR__.'/../../../../../../web/uploads/pictures', $user->getPicture());
        }
    }
}
