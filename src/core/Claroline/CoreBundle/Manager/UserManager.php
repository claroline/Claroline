<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Writer\UserWriter;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Workspace\Creator;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    private $userRepo;
    private $roleRepo;
    private $userWriter;
    private $roleManager;
    private $workspaceManager;
    private $ed;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" = @DI\Inject("user_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "userWriter" = @DI\Inject("claroline.writer.user_writer"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.workspace.creator"),
     *     "ed" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __contruct(
        UserRepository $userRepo,
        RoleRepository $roleRepo,
        UserWriter $userWriter,
        RoleManager $roleManager,
        Creator $workspaceManager,
        $ed
    )
    {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
        $this->userWriter = $userWriter;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->ed = $ed;
    }

    public function insertUser(User $user)
    {
        $this->userWriter->insertUser($user);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function insertUserWithRoles(User $user, ArrayCollection $roles)
    {
        $this->userWriter->insertUser($user);
        $this->roleManager->associateRoles($user, $roles);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function deleteUser(User $user)
    {
        $this->userWriter->deleteUser($user);
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
        $user = $this->userWriter->createUser(
            $firstName,
            $lastName,
            $username,
            $pwd,
            $code,
            $email,
            $phone,
            $workspace
        );
        $this->roleManager->associateRoles($user, $roles);

        $log = new LogUserCreateEvent($user);
        $this->ed->dispatch('log', $log);
    }

    public function importUsers($users)
    {
        $role = $this->roleRepo->findOneBy(array('name' => 'ROLE_USER'));

        foreach ($users as $user) {
            $firstName = $user[0];
            $lastName = $user[1];
            $username = $user[2];
            $pwd = $user[3];
            $code = $user[4];
            $email = isset($user[5])? $user[5] : null;

            $newUser = $this->userWriter->createUser(
                $firstName,
                $lastName,
                $username,
                $pwd,
                $code,
                $email,
                null,
                null
            );
            $this->roleManager->associateRoles($newUser, $role);

            $log = new LogUserCreateEvent($newUser);
            $this->ed->dispatch('log', $log);
        }
    }
}