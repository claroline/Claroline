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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserDisableEvent;
use Claroline\CoreBundle\Event\Security\UserEnableEvent;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Messenger\Message\DisableInactiveUsers;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class UserManager
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var UserRepository */
    private $userRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        ObjectManager $om,
        Crud $crud,
        PlatformConfigurationHandler $platformConfigHandler,
        StrictDispatcher $dispatcher,
        MessageBusInterface $messageBus
    ) {
        $this->crud = $crud;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->dispatcher = $dispatcher;
        $this->messageBus = $messageBus;

        $this->userRepo = $om->getRepository(User::class);
        $this->roleRepo = $om->getRepository(Role::class);
    }

    /**
     * @param string $username
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
        $roles = $this->roleRepo->findAllPlatformRoles();
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

    public function validateEmailHash($validationHash): bool
    {
        $user = $this->getByEmailValidationHash($validationHash);
        if ($user) {
            $user->setIsMailValidated(true);

            $this->om->persist($user);
            $this->om->flush();

            return true;
        }

        return false;
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

    public function setInitDate(User $user)
    {
        if (null === $user->getInitDate()) {
            $this->setUserInitDate($user);
        }

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

    public function enable(User $user)
    {
        if (!$user->isEnabled()) {
            $user->enable();
            $this->om->persist($user);
            $this->om->flush();

            $this->dispatcher->dispatch(SecurityEvents::USER_ENABLE, UserEnableEvent::class, [$user]);
        }

        return $user;
    }

    public function disable(User $user)
    {
        if ($user->isEnabled()) {
            $user->disable();
            $this->om->persist($user);
            $this->om->flush();

            $this->dispatcher->dispatch(SecurityEvents::USER_DISABLE, UserDisableEvent::class, [$user]);
        }

        return $user;
    }

    public function disableInactive(\DateTimeInterface $lastActivity)
    {
        $this->messageBus->dispatch(new DisableInactiveUsers($lastActivity));
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
            $roleAdmin = $this->roleRepo->findOneBy(['name' => 'ROLE_ADMIN']);
            $user->addRole($roleAdmin);

            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
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

    public function hasReachedLimit(): bool
    {
        $usersLimitReached = false;

        if ($this->platformConfigHandler->getParameter('restrictions.users')) {
            $usersCount = $this->countEnabledUsers();

            if ($usersCount >= $this->platformConfigHandler->getParameter('restrictions.users')) {
                $usersLimitReached = true;
            }
        }

        return $usersLimitReached;
    }

    public function merge(User $keep, User $remove)
    {
        // Dispatching an event for letting plugins and core do what they need to do
        /** @var MergeUsersEvent $event */
        $event = $this->dispatcher->dispatch(
            'merge_users',
            'User\MergeUsers',
            [
                $keep,
                $remove,
            ]
        );

        $keep_username = $keep->getUsername();
        $remove_username = $remove->getUsername();

        // Delete old user
        $this->crud->deleteBulk([$remove], [Options::SOFT_DELETE]);

        $event->addMessage("[CoreBundle] user removed: $remove_username");
        $event->addMessage("[CoreBundle] user kept: $keep_username");

        return $event->getMessages();
    }
}
