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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\LogBundle\Messenger\Security\Message\UserDisableMessage;
use Claroline\LogBundle\Messenger\Security\Message\UserEnableMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var Security */
    private $security;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ObjectManager $om,
        Crud $crud,
        PlatformConfigurationHandler $platformConfigHandler,
        MessageBusInterface $messageBus,
        Security $security,
        TranslatorInterface $translator
    ) {
        $this->crud = $crud;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->messageBus = $messageBus;
        $this->security = $security;
        $this->translator = $translator;

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

    public function enable(User $user)
    {
        $user->enable();
        $this->om->persist($user);
        $this->om->flush();

        $this->messageBus->dispatch(new UserEnableMessage(
            $user->getId(),
            $this->security->getUser()->getId(),
            $this->translator->trans('userEnable', ['username' => $user->getUsername()], 'security')
        ));

        return $user;
    }

    public function disable(User $user)
    {
        $user->disable();
        $this->om->persist($user);
        $this->om->flush();

        $this->messageBus->dispatch(new UserDisableMessage(
            $user->getId(),
            $this->security->getUser()->getId(),
            $this->translator->trans('userDisable', ['username' => $user->getUsername()], 'security')
        ));

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
