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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserOptions;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Security\PlatformRoles;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    use LoggableTrait;

    const MAX_USER_BATCH_SIZE = 100;
    const MAX_EDIT_BATCH_SIZE = 100;

    private $container;
    private $mailManager;
    private $objectManager;
    private $organizationManager;
    private $platformConfigHandler;
    private $roleManager;
    private $strictEventDispatcher;
    private $tokenStorage;
    private $translator;
    private $validator;
    private $workspaceManager;
    /** @var UserRepository */
    private $userRepo;

    /**
     * UserManager Constructor.
     *
     * @param ContainerInterface           $container
     * @param MailManager                  $mailManager
     * @param ObjectManager                $objectManager
     * @param OrganizationManager          $organizationManager
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param RoleManager                  $roleManager
     * @param StrictDispatcher             $strictEventDispatcher
     * @param TokenStorageInterface        $tokenStorage
     * @param TranslatorInterface          $translator
     * @param ValidatorInterface           $validator
     * @param WorkspaceManager             $workspaceManager
     */
    public function __construct(
        ContainerInterface $container,
        MailManager $mailManager,
        ObjectManager $objectManager,
        OrganizationManager $organizationManager,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        StrictDispatcher $strictEventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        WorkspaceManager $workspaceManager)
    {
        $this->container = $container;
        $this->mailManager = $mailManager;
        $this->objectManager = $objectManager;
        $this->organizationManager = $organizationManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->strictEventDispatcher = $strictEventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->workspaceManager = $workspaceManager;
        $this->userRepo = $objectManager->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     *
     * @todo use crud instead
     * @todo REMOVE ME (caution: this is used to create users in Command\User\CreateCommand and default User in fixtures, and other things)
     *
     * @param User  $user
     * @param array $options
     * @param array $rolesToAdd
     *
     * @return User
     *
     * @deprecated
     */
    public function createUser(
        User $user,
        array $options = [],
        $rolesToAdd = []
    ) {
        $this->objectManager->startFlushSuite();
        $additionalRoles = [];

        $options = array_merge($options, [Options::ADD_NOTIFICATIONS]);

        foreach ($rolesToAdd as $roleToAdd) {
            $additionalRoles[] = is_string($roleToAdd) ? $this->roleManager->getRoleByName($roleToAdd) : $roleToAdd;
        }

        foreach ($additionalRoles as $role) {
            if ($role) {
                $this->roleManager->associateRole($user, $role);
            }
        }

        $this->container->get('claroline.crud.user')->create($user, $options);
        $this->objectManager->endFlushSuite();

        return $user;
    }

    /**
     * Creates the personal workspace of a user.
     *
     * @param User      $user
     * @param Workspace $model
     */
    public function setPersonalWorkspace(User $user, Workspace $model = null)
    {
        $locale = $this->platformConfigHandler->getParameter('locale_language');
        $this->translator->setLocale($locale);
        $created = $this->objectManager->getRepository(Workspace::class)->findOneBy(['code' => $user->getUsername()]);

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

        $this->objectManager->persist($user);
        $this->objectManager->flush();
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

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
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
     * @param int[] $ids
     *
     * @deprecated ObjectManager can handle it
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids)
    {
        return $this->objectManager->findByIds('Claroline\CoreBundle\Entity\User', $ids);
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

    public function getUsersExcludingRoles(array $roles, $offset = null, $limit = null)
    {
        return $this->userRepo->findUsersExcludingRoles($roles, $offset, $limit);
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
     * @todo use finder instead
     * @todo REMOVE ME
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
        $this->objectManager->persist($user);
        $this->objectManager->flush();
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
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string                            $locale Language with format en, fr, es, etc
     */
    public function setLocale(User $user, $locale = 'en')
    {
        $user->setLocale($locale);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
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
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }
    }

    public function getUserByUsernameOrMail($username, $email, $executeQuery = true)
    {
        return $this->userRepo->findUserByUsernameOrMail(
            $username,
            $email,
            $executeQuery
        );
    }

    public function countEnabledUsers(array $organizations = [])
    {
        return $this->userRepo->countUsers($organizations);
    }

    /**
     * Activates a User and set the init date to now.
     *
     * @param User $user
     */
    public function activateUser(User $user)
    {
        $user->setIsEnabled(true);
        $user->setIsMailValidated(true);
        $user->setResetPasswordHash(null);
        $user->setInitDate(new \DateTime());

        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Logs the current user.
     *
     * @param User $user
     */
    public function logUser(User $user)
    {
        //need the refresh for some reason...
        /** @var User $user */
        $user = $this->objectManager->getRepository(User::class)->findOneBy([
            'username' => $user->getUsername(),
        ]);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserLogin', [$user]);

        if (null === $user->getInitDate()) {
            $this->setUserInitDate($user);
        }
        $user->setLastLogin(new \DateTime());

        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function persistUserOptions(UserOptions $options)
    {
        $this->objectManager->persist($options);
        $this->objectManager->flush();
    }

    public function getUserOptions(User $user)
    {
        $options = $user->getOptions();

        if (is_null($options)) {
            $options = new UserOptions();
            $options->setUser($user);
            $this->objectManager->persist($options);
            $user->setOptions($options);
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }

        return $options;
    }

    // TODO : remove me, only used by claco form which should use standard picker
    public function getAllVisibleUsersIdsForUserPicker(User $user)
    {
        $usersIds = [];
        $roles = $this->generateRoleRestrictions($user);
        $groups = $this->generateGroupRestrictions($user);
        $workspaces = $this->generateWorkspaceRestrictions($user);
        $users = $this->userRepo->findUsersForUserPicker(
            '',
            false,
            false,
            false,
            'lastName',
            'ASC',
            $roles,
            $groups,
            $workspaces
        );

        /** @var User $user */
        foreach ($users as $user) {
            $usersIds[] = $user->getId();
        }

        return $usersIds;
    }

    private function generateRoleRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $wsRoles = $this->roleManager->getWorkspaceRolesByUser($user);

            foreach ($wsRoles as $wsRole) {
                $wsRoleId = $wsRole->getId();
                $workspace = $wsRole->getWorkspace();
                $guid = $workspace->getGuid();
                $managerRoleName = 'ROLE_WS_MANAGER_'.$guid;

                if ($wsRole->getName() === $managerRoleName) {
                    $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);

                    foreach ($workspaceRoles as $workspaceRole) {
                        $workspaceRoleId = $workspaceRole->getId();

                        if (!isset($restrictions[$workspaceRoleId])) {
                            $restrictions[$workspaceRoleId] = $workspaceRole;
                        }
                    }
                } elseif (!isset($restrictions[$wsRoleId])) {
                    $restrictions[$wsRoleId] = $wsRole;
                }
            }
        }

        return $restrictions;
    }

    private function generateGroupRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $restrictions = $user->getGroups()->toArray();
        }

        return $restrictions;
    }

    private function generateWorkspaceRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $restrictions = $this->workspaceManager->getWorkspacesByUser($user);
        }

        return $restrictions;
    }

    public function initializePassword(User $user)
    {
        $user->setHashTime(time());
        $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
        $user->setResetPasswordHash($password);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function hideEmailValidation(User $user)
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * This method will bind each users who don't already have an organization to the default one.
     */
    public function bindUserToOrganization()
    {
        $limit = 250;
        $offset = 0;
        $this->log('Add organizations to users...');
        $this->objectManager->startFlushSuite();
        $countUsers = $this->objectManager->count('ClarolineCoreBundle:User');
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
                    $this->objectManager->persist($user);

                    if (0 === $i % 250) {
                        $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
                        $this->objectManager->forceFlush();
                    }
                } else {
                    $this->log("Organization for user {$user->getUsername()} already exists");
                }
            }

            $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
            $this->objectManager->forceFlush();
            $default = $this->organizationManager->getDefault();

            $offset += $limit;
        }

        $this->objectManager->endFlushSuite();
    }

    /**
     * This method will bind each users who don't already have an organization to the default one.
     *
     * @deprecated only used in Updater120304
     */
    public function bindUserToGroup()
    {
        $limit = 250;
        $offset = 0;
        $this->log('Add default group to users...');
        $this->objectManager->startFlushSuite();
        $countUsers = $this->objectManager->count('ClarolineCoreBundle:User');
        /** @var Group $default */
        $default = $this->objectManager->getRepository(Group::class)->findOneBy(['name' => PlatformRoles::USER]);
        $i = 0;

        while ($offset < $countUsers) {
            $users = $this->userRepo->findBy([], null, $limit, $offset);

            /** @var User $user */
            foreach ($users as $user) {
                if (!$user->hasGroup($default)) {
                    ++$i;
                    $this->log('Add default group for user '.$user->getUsername());
                    $user->addGroup($default);
                    $this->objectManager->persist($user);

                    if (0 === $i % 250) {
                        $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
                        $this->objectManager->forceFlush();
                    }
                } else {
                    $this->log("group for user {$user->getUsername()} already exists");
                }
            }

            $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
            $this->objectManager->forceFlush();
            $default = $this->objectManager->getRepository(Group::class)->findOneByName(PlatformRoles::USER);

            $offset += $limit;
        }

        $this->objectManager->endFlushSuite();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function enable(User $user)
    {
        $user->enable();
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function disable(User $user)
    {
        $user->disable();
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function getDefaultClarolineAdmin()
    {
        $user = $this->getUserByUsername('claroline-connect');

        if (!$user) {
            $user = new User();

            $user->setUsername('claroline-connect');
            $user->setFirstName('Claroline');
            $user->setLastName('Connect');
            $user->setEmail('support@claroline.com');
            $user->setPlainPassword(uniqid('', true));
            $user->setAcceptedTerms(true);

            $user->disable();
            $user->remove();

            $this->createUser($user, [Options::NO_EMAIL, Options::NO_PERSONAL_WORKSPACE]);
        }

        $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $user->addRole($roleAdmin);
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function getDefaultClarolineUser()
    {
        $user = $this->getUserByUsername('claroline-connect-user');

        if (!$user) {
            $user = new User();

            $user->setUsername('claroline-connect-user');
            $user->setFirstName('claroline-connect-user');
            $user->setLastName('claroline-connect-user');
            $user->setEmail('claroline-connect-user');
            $user->setPlainPassword(uniqid('', true));
            $user->setAcceptedTerms(true);
            $user->disable();
            $user->remove();

            $this->createUser($user, [Options::NO_EMAIL, Options::NO_PERSONAL_WORKSPACE]);
        }

        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $user->addRole($roleUser);
        $this->objectManager->persist($user);
        $this->objectManager->flush();

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
        $this->objectManager->startFlushSuite();

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
                    $this->objectManager->forceFlush();
                    $flushed = true;
                }
            }
            if (!$flushed) {
                $this->log('Flushing, this may be very long for large databases');
                $this->objectManager->forceFlush();
            }
            $this->objectManager->clear();
        }
        $this->objectManager->endFlushSuite();
    }

    public function checkPersonalWorkspaceIntegrityForUser(User $user, $i = 1, $totalUsers = 1)
    {
        $this->log('Checking personal workspace for '.$user->getUsername()." ($i/$totalUsers)");
        $ws = $user->getPersonalWorkspace();
        $managerRole = $ws->getManagerRole();
        if (!$user->hasRole($managerRole->getRole())) {
            $this->log('Adding user as manager to his personal workspace', LogLevel::DEBUG);
            $this->objectManager->startFlushSuite();
            $user->addRole($managerRole);
            $this->objectManager->persist($user);
            $this->objectManager->endFlushSuite();
        }
    }

    /**
     * Merges two users and transfers every resource to the kept user.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function transferRoles(User $from, User $to)
    {
        $roles = $from->getEntityRoles();

        foreach ($roles as $role) {
            $to->addRole($role);
        }

        $this->objectManager->flush();

        return count($roles);
    }

    public function sendResetPassword(User $user)
    {
        $user->setHashTime(time());
        $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
        $user->setResetPasswordHash($password);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
        $this->mailManager->sendForgotPassword($user);
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
