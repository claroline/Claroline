<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    private $userRepo;
    private $roleRepo;
    private $toolRepo;
    private $writer;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $ed;
    private $personalWsTemplateFile;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" = @DI\Inject("user_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "toolRepo" = @DI\Inject("tool_repository"),
     *     "writer" = @DI\Inject("claroline.database.writer"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "trans" = @DI\Inject("translator"),
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        UserRepository $userRepo,
        RoleRepository $roleRepo,
        ToolRepository $toolRepo,
        Writer $writer,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager,
        $ed,
        $personalWsTemplateFile,
        Translator $trans,
        PlatformConfigurationHandler $ch
    )
    {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
        $this->toolRepo = $toolRepo;
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->trans = $trans;
        $this->ch = $ch;
    }

    public function createUser(User $user)
    {
        $role = $this->roleRepo->findOneBy(array('name' => 'ROLE_USER'));
        $user->addRole($role);
        $this->setPersonalWorkspace($user);
        $this->addRequiredTools($user, $this->findRequiredTools());

        $this->writer->update($user);
    }

    public function createUserWithRole(User $user, $roleName)
    {
        $role = $this->roleRepo->findOneBy(array('name' => $roleName));
        $user->addRole($role);
        $this->setPersonalWorkspace($user);
        $this->addRequiredTools($user, $this->findRequiredTools());

        $this->writer->update($user);
    }

    public function insertUser(User $user)
    {
        $this->writer->create($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function insertUserWithRoles(User $user, ArrayCollection $roles)
    {
        $this->writer->create($user);
        $this->roleManager->associateRoles($user, $roles);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function deleteUser(User $user)
    {
        $this->writer->delete($user);
    }

    public function createUserWithRoles(
        $firstName,
        $lastName,
        $username,
        $pwd,
        $code,
        $email,
        $phone,
        array $roles,
        AbstractWorkspace $workspace = null
    )
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setPlainPassword($pwd);
        $user->setAdministrativeCode($code);
        $user->setMail($email);
        $user->setPhone($phone);
        $user->setPersonalWorkspace($workspace);

        $this->writer->create($user);
        $this->roleManager->associateRoles($user, $roles);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function importUsers($users)
    {
        $role = $this->roleRepo->findOneBy(array('name' => 'ROLE_USER'));
        $requiredTools = $this->findRequiredTools();

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
            $newUser->addRole($role);
            $this->addRequiredTools($newUser, $requiredTools);

            $this->writer->create($newUser);

            $log = new LogUserCreateEvent($newUser);
            $this->ed->dispatch('log', $log);
        }
    }

    private function addRequiredTools(User $user, array $requiredTools)
    {
        $i = 1;

        foreach ($requiredTools as $requiredTool) {
            $this->toolManager->addDesktopTool($requiredTool, $user, $i, $requiredTool->getName());
            $i++;
        }
    }

    private function findRequiredTools()
    {
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'home'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'resource_manager'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'parameters'));

        return $requiredTools;
    }

    private function setPersonalWorkspace(User $user)
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
}
