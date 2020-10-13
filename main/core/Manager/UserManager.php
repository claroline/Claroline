<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserManager implements LoggerAwareInterface
{
    use LoggableTrait;

    private $crud;
    private $om;
    private $organizationManager;
    private $platformConfigHandler;
    private $roleManager;
    private $translator;
    private $workspaceManager;
    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        ObjectManager $om,
        Crud $crud,
        OrganizationManager $organizationManager,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager)
    {
        $this->crud = $crud;
        $this->om = $om;
        $this->organizationManager = $organizationManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * Creates the personal workspace of a user.
     */
    public function setPersonalWorkspace(User $user, Workspace $model = null)
    {
        $locale = $this->platformConfigHandler->getParameter('locale_language');
        $this->translator->setLocale($user->getLocale() ?? $locale);
        $created = $this->om->getRepository(Workspace::class)->findOneBy(['code' => $user->getUsername()]);

        if ($created) {
            $code = $user->getUsername().'~'.uniqid();
        } else {
            $code = $user->getUsername();
        }

        $personalWorkspaceName = $this->translator->trans('personal_workspace', [], 'platform').' - '.$user->getUsername();
        $workspace = new Workspace();
        $workspace->setCode($code);
        $workspace->setName($personalWorkspaceName);
        $workspace->setCreator($user);

        $workspace = !$model ?
            $this->workspaceManager->copy($this->workspaceManager->getDefaultModel(true), $workspace) :
            $this->workspaceManager->copy($model, $workspace);

        $workspace->setPersonal(true);

        $user->setPersonalWorkspace($workspace);
        $user->addRole($workspace->getManagerRole());

        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * @param string $username
     *
     * @todo use finder instead
     * @todo REMOVE ME
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            $user = null;
        }

        return $user;
    }

    public function countUsersForPlatformRoles($organizations = null)
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $roleNames = array_map(function (Role $r) {return $r->getName(); }, $roles);
        $usersInRoles = [];
        foreach ($roles as $role) {
            $restrictionRoleNames = null;
            if ('ROLE_USER' === $role->getName()) {
                $restrictionRoleNames = array_diff($roleNames, ['ROLE_USER']);
            } elseif ('ROLE_WS_CREATOR' !== $role->getName() && 'ROLE_ADMIN' !== $role->getName()) {
                $restrictionRoleNames = ['ROLE_WS_CREATOR', 'ROLE_ADMIN'];
            } elseif ('ROLE_ADMIN' !== $role->getName()) {
                $restrictionRoleNames = ['ROLE_ADMIN'];
            }
            $usersInRoles[] = [
                'name' => $role->getTranslationKey(),
                'total' => floatval($this->userRepo->countUsersByRole($role, $restrictionRoleNames, $organizations)),
            ];
        }

        return $usersInRoles;
    }

    /**
     * @param int $max
     *
     * @return User[]
     */
    public function getUsersEnrolledInMostWorkspaces($max, $organizations = null)
    {
        return $this->userRepo->findUsersEnrolledInMostWorkspaces($max, $organizations);
    }

    /**
     * @param int $max
     *
     * @return User[]
     */
    public function getUsersOwnersOfMostWorkspaces($max, $organizations = null)
    {
        return $this->userRepo->findUsersOwnersOfMostWorkspaces($max, $organizations);
    }

    /**
     * @param string $resetPassword
     *
     * @return User
     */
    public function getByResetPasswordHash($resetPassword)
    {
        /** @var User $user */
        $user = $this->userRepo->findOneBy(['resetPasswordHash' => $resetPassword]);

        return $user;
    }

    /**
     * @param string $validationHash
     *
     * @return User
     */
    public function getByEmailValidationHash($validationHash)
    {
        /** @var User $user */
        $user = $this->userRepo->findOneBy(['emailValidationHash' => $validationHash]);

        return $user;
    }

    public function validateEmailHash($validationHash)
    {
        /** @var User $user */
        $user = $this->getByEmailValidationHash($validationHash);
        $user->setIsMailValidated(true);

        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * @return User[]
     *
     * @todo use finder instead
     * @todo REMOVE ME
     */
    public function getAllEnabledUsers($executeQuery = true)
    {
        return $this->userRepo->findAllEnabledUsers($executeQuery);
    }

    /**
     * Set the user locale.
     *
     * @todo use crud instead
     * @todo REMOVE ME
     *
     * @param string $locale Language with format en, fr, es, etc
     */
    public function setLocale(User $user, $locale = 'en')
    {
        $user->setLocale($locale);
        $this->om->persist($user);
        $this->om->flush();
    }

    public function countUsersByRoleIncludingGroup(Role $role)
    {
        return $this->userRepo->countUsersByRoleIncludingGroup($role);
    }

    public function countUsersOfGroup(Group $group)
    {
        return $this->userRepo->countUsersOfGroup($group);
    }

    public function setUserInitDate(User $user)
    {
        $accountDuration = $this->platformConfigHandler->getParameter('account_duration');
        if ($accountDuration) {
            $expirationDate = new \DateTime();
            $expirationYear = (strtotime('2100-01-01')) ? 2100 : 2038;

            (null === $accountDuration) ?
                $expirationDate->setDate($expirationYear, 1, 1) :
                $expirationDate->add(new \DateInterval('P'.$accountDuration.'D'));

            $user->setExpirationDate($expirationDate);
            $user->setInitDate(new \DateTime());
            $this->om->persist($user);
            $this->om->flush();
        }
    }

    public function countEnabledUsers(array $organizations = [])
    {
        return $this->userRepo->countUsers($organizations);
    }

    /**
     * Activates a User and set the init date to now.
     */
    public function activateUser(User $user)
    {
        $user->setIsEnabled(true);
        $user->setIsMailValidated(true);
        $user->setResetPasswordHash(null);
        $user->setInitDate(new \DateTime());

        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * Updates user last login date.
     */
    public function updateLastLogin(User $user)
    {
        if (null === $user->getInitDate()) {
            $this->setUserInitDate($user);
        }
        $user->setLastLogin(new \DateTime());

        $this->om->persist($user);
        $this->om->flush();
    }

    public function initializePassword(User $user)
    {
        $user->setHashTime(time());
        $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
        $user->setResetPasswordHash($password);
        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * This method will bind each users who don't already have an organization to the default one.
     */
    public function bindUserToOrganization()
    {
        $limit = 250;
        $offset = 0;
        $this->log('Add organizations to users...');
        $this->om->startFlushSuite();
        $countUsers = $this->om->count('ClarolineCoreBundle:User');
        $default = $this->organizationManager->getDefault();
        $i = 0;

        while ($offset < $countUsers) {
            $users = $this->userRepo->findBy([], null, $limit, $offset);

            /** @var User $user */
            foreach ($users as $user) {
                $this->log('Setting user administrated organization...');

                foreach ($user->getAdministratedOrganizations() as $organization) {
                    //I know this is weird but the setter is now in this method (it used to not exist)
                    $user->addAdministratedOrganization($organization);
                }

                if (0 === count($user->getOrganizations())) {
                    ++$i;
                    $this->log('Add default organization for user '.$user->getUsername());
                    $user->addOrganization($default);
                    $this->om->persist($user);

                    if (0 === $i % 250) {
                        $this->log("Flushing... [UOW = {$this->om->getUnitOfWork()->size()}]");
                        $this->om->forceFlush();
                    }
                } else {
                    $this->log("Organization for user {$user->getUsername()} already exists");
                }
            }

            $this->log("Flushing... [UOW = {$this->om->getUnitOfWork()->size()}]");
            $this->om->forceFlush();
            $default = $this->organizationManager->getDefault();

            $offset += $limit;
        }

        $this->om->endFlushSuite();
    }

    public function enable(User $user)
    {
        $user->enable();
        $this->om->persist($user);
        $this->om->flush();

        return $user;
    }

    public function disable(User $user)
    {
        $user->disable();
        $this->om->persist($user);
        $this->om->flush();

        return $user;
    }

    public function getDefaultClarolineAdmin()
    {
        $user = $this->getUserByUsername('claroline-connect');

        if (!$user) {
            $user = $this->crud->create(User::class, [
                'firstName' => 'Claroline',
                'lastName' => 'Connect',
                'username' => 'claroline-connect',
                'email' => 'support@claroline.com',
                'plainPassword' => uniqid('', true),
                'restrictions' => [
                    'disabled' => true,
                    'removed' => true,
                ],
            ], [Options::NO_EMAIL, Options::NO_PERSONAL_WORKSPACE]);
        }

        if (!$user->hasRole('ROLE_ADMIN')) {
            $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
            $user->addRole($roleAdmin);

            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }

    public function getDefaultClarolineUser()
    {
        $user = $this->getUserByUsername('claroline-connect-user');
        if (!$user) {
            $user = $this->crud->create(User::class, [
                'firstName' => 'claroline-connect-user',
                'lastName' => 'claroline-connect-user',
                'username' => 'claroline-connect-user',
                'email' => 'claroline-connect-user',
                'plainPassword' => uniqid('', true),
                'restrictions' => [
                    'disabled' => true,
                    'removed' => true,
                ],
            ], [Options::NO_EMAIL, Options::NO_PERSONAL_WORKSPACE]);
        }

        if (!$user->hasRole('ROLE_USER')) {
            $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
            $user->addRole($roleUser);

            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }

    public function checkPersonalWorkspaceIntegrity()
    {
        // Get all users having problem seeing their personal workspace
        $cntUsers = $this->userRepo->countUsersNotManagersOfPersonalWorkspace();
        $this->log("Found $cntUsers users whose personal workspace needs to get fixed");
        $batchSize = 1000;
        $flushSize = 250;
        $i = 0;
        $flushed = true;
        $this->om->startFlushSuite();

        for ($batch = 0; $batch < ceil($cntUsers / $batchSize); ++$batch) {
            $users = $this->userRepo->findUsersNotManagersOfPersonalWorkspace(0, $batchSize);
            $nb = count($users);
            $this->log("Fetched {$nb} users for checking");
            foreach ($users as $user) {
                ++$i;
                $flushed = false;
                $this->checkPersonalWorkspaceIntegrityForUser($user, $i, $cntUsers);

                if (0 === $i % $flushSize) {
                    $this->log('Flushing, this may be very long for large databases');
                    $this->om->forceFlush();
                    $flushed = true;
                }
            }
            if (!$flushed) {
                $this->log('Flushing, this may be very long for large databases');
                $this->om->forceFlush();
            }
            $this->om->clear();
        }
        $this->om->endFlushSuite();
    }

    public function checkPersonalWorkspaceIntegrityForUser(User $user, $i = 1, $totalUsers = 1)
    {
        $this->log('Checking personal workspace for '.$user->getUsername()." ($i/$totalUsers)");
        $ws = $user->getPersonalWorkspace();
        $managerRole = $ws->getManagerRole();
        if (!$user->hasRole($managerRole->getRole())) {
            $this->log('Adding user as manager to his personal workspace', LogLevel::DEBUG);
            $this->om->startFlushSuite();
            $user->addRole($managerRole);
            $this->om->persist($user);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Merges two users and transfers every resource to the kept user.
     *
     * @return int
     */
    public function transferRoles(User $from, User $to)
    {
        $roles = $from->getEntityRoles();

        foreach ($roles as $role) {
            $to->addRole($role);
        }

        $this->om->flush();

        return count($roles);
    }

    public function hasReachedLimit()
    {
        $usersLimitReached = false;

        if ($this->platformConfigHandler->getParameter('restrictions.users') &&
            $this->platformConfigHandler->getParameter('restrictions.max_users')
        ) {
            $usersCount = $this->countEnabledUsers();

            if ($usersCount >= $this->platformConfigHandler->getParameter('restrictions.max_users')) {
                $usersLimitReached = true;
            }
        }

        return $usersLimitReached;
    }
}
