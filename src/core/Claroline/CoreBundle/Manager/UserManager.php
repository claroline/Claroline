<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" = @DI\Inject("user_repository"),
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
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
        $this->personalWsTemplateFile = $personalWsTemplateFile."default.zip";
        $this->trans = $trans;
        $this->ch = $ch;
    }

    public function insertUsert(User $user)
    {
        $this->writer->create($user);
    }

    public function createUser(User $user)
    {
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, PlatformRoles::USER);

        $this->writer->create($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);

        return $user;
    }

    public function createUserWithRole(User $user, $roleName)
    {
        $this->setPersonalWorkspace($user);
        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, $roleName);

        $this->writer->create($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);

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

            $this->setPersonalWorkspace($user);
            $this->toolManager->addRequiredToolsToUser($user);
            $this->roleManager->setRoleToRoleSubject($newUser, $roleName);

            $this->writer->create($newUser);

            $log = new LogUserCreateEvent($newUser);
            $this->ed->dispatch('log', $log);
        }
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
