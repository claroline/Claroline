<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\Security\Core\User\UserInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\Event\Log\LogUserCreateEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Database\GenericRepository;
use Claroline\CoreBundle\Pager\PagerFactory;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    private $userRepo;
    private $writer;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $ed;
    private $personalWsTemplateFile;
    private $trans;
    private $ch;
    private $genericRepo;
    private $pagerFactory;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" =               @DI\Inject("user_repository"),
     *     "writer" =                 @DI\Inject("claroline.database.writer"),
     *     "roleManager" =            @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager" =       @DI\Inject("claroline.manager.workspace_manager"),
     *     "toolManager" =            @DI\Inject("claroline.manager.tool_manager"),
     *     "ed" =                     @DI\Inject("claroline.event.event_dispatcher"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "trans"                  = @DI\Inject("translator"),
     *     "ch"                     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "genericRepo"            = @DI\Inject("claroline.database.generic_repository"),
     *     "pagerFactory"           = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        UserRepository $userRepo,
        Writer $writer,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager,
        StrictDispatcher $ed,
        $personalWsTemplateFile,
        Translator $trans,
        PlatformConfigurationHandler $ch,
        GenericRepository $genericRepo,
        PagerFactory $pagerFactory
    )
    {
        $this->userRepo = $userRepo;
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->trans = $trans;
        $this->ch = $ch;
        $this->genericRepo = $genericRepo;
        $this->pagerFactory = $pagerFactory;
    }

    public function insertUser(User $user)
    {
        $this->writer->create($user);
    }

    public function createUser(User $user)
    {
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, PlatformRoles::USER);

        $this->writer->create($user);

        $this->ed->dispatch('log', 'LogUserCreate', $user);

        return $user;
    }

    public function deleteUser(User $user)
    {
        $this->writer->delete($user);
    }

    public function createUserWithRole(User $user, $roleName)
    {
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, $roleName);

        $this->writer->create($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', 'Log\LogUserCreateEvent', array($user));

        return $user;
    }

    public function insertUserWithRoles(User $user, ArrayCollection $roles)
    {
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->associateRoles($user, $roles);

        $this->writer->create($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function importUsers($users)
    {
        $roleName = PlatformRoles::USER;

        foreach ($users as $user) {
            $firstName = $user[0];
            $lastName = $user[1];
            $username = $user[2];
            $pwd = $user[3];
            $code = $user[4];
            $email = isset($user[5])? $user[5] : null;

            $newUser = new User();
            $newUser->setFirstName($firstName);
            $newUser->setLastName($lastName);
            $newUser->setUsername($username);
            $newUser->setPlainPassword($pwd);
            $newUser->setAdministrativeCode($code);
            $newUser->setMail($email);

            $this->setPersonalWorkspace($newUser);
            $this->toolManager->addRequiredToolsToUser($newUser);
            $this->roleManager->setRoleToRoleSubject($newUser, $roleName);

            $this->writer->create($newUser);

            $log = new LogUserCreateEvent($newUser);
            $this->ed->dispatch('log', $log);
        }
    }

    public function setPersonalWorkspace(User $user)
    {
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->ch->getParameter('locale_language');
        $this->trans->setLocale($locale);
        $personalWorkspaceName = $this->trans->trans('personal_workspace', array(), 'platform');
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());

        $workspace = $this->workspaceManager->create($config, $user);
        $user->setPersonalWorkspace($workspace);
        $this->writer->update($user);
    }

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

    public function getUserByUsername($username)
    {
        return $this->userRepo->loadUserByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
    }

    public function getUserByWorkspaceAndRole(AbstractWorkspace $workspace, Role $role)
    {
        return $this->userRepo->findByWorkspaceAndRole($workspace, $role);
    }

    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $page)
    {
        $query = $this->userRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $page)
    {
        $query = $this->userRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getAllUsers($page)
    {
        $query = $this->userRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getUsersByName($search, $page)
    {
        $query = $this->userRepo->findByName($search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getUsersByGroup(Group $group, $page)
    {
        $query = $this->userRepo->findByGroup($group, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getUsersByNameAndGroup($search, Group $group, $page)
    {
        $query = $this->userRepo->findByNameAndGroup($search, $group, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getUsersByWorkspace(AbstractWorkspace $workspace, $page)
    {
        $query = $this->userRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getUsersByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page)
    {
        $query = $this->userRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroupOutsiders(Group $group, $page)
    {
        $query = $this->userRepo->findGroupOutsiders($group, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroupOutsidersByName(Group $group, $search, $getQuery = false)
    {
        $query = $this->userRepo->findGroupOutsidersByName($group, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getAllUsersExcept(User $excludedUser)
    {
        return $this->userRepo->findAllExcept($excludedUser);
    }

    public function getUsersByUsernames(array $usernames)
    {
        return $this->userRepo->findByUsernames($usernames);
    }

    public function getNbUsers()
    {
        return $this->userRepo->count();
    }

    public function getUsersByIds(array $ids)
    {
        return $this->genericRepo->findByIds('Claroline\CoreBundle\Entity\User', $ids);
    }

    public function getUsersEnrolledInMostWorkspaces ($max)
    {
        return $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
    }

    public function getUsersOwnersOfMostWorkspaces ($max)
    {
        return $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
    }

    public function getUserById($userId)
    {
        return $this->userRepo->find($userId);
    }
}
