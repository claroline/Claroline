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
use Claroline\CommunityBundle\Messenger\Message\DisableInactiveUsers;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserDisableEvent;
use Claroline\CoreBundle\Event\Security\UserEnableEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Messenger\MessageBusInterface;

class UserManager
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var MessageBusInterface */
    private $messageBus;

    /** @var UserRepository */
    private $userRepo;
    /** @var RoleRepository */
    private $roleRepo;

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

    public function getUserByUsername(string $username): ?User
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            $user = null;
        }

        return $user;
    }

    public function getByResetPasswordHash(string $resetPassword): ?User
    {
        return $this->userRepo->findOneBy(['resetPasswordHash' => $resetPassword]);
    }

    public function getByEmailValidationHash(string $validationHash): ?User
    {
        return $this->userRepo->findOneBy(['emailValidationHash' => $validationHash]);
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
     * @todo REMOVE ME. use crud instead
     */
    public function setLocale(User $user, ?string $locale = 'en'): void
    {
        $user->setLocale($locale);

        $this->om->persist($user);
        $this->om->flush();
    }

    public function countEnabledUsers(?array $organizations = []): int
    {
        return $this->userRepo->countUsers($organizations);
    }

    /**
     * Activates a User and set the init date to now.
     */
    public function activateUser(User $user): void
    {
        $user->setIsEnabled(true);
        $user->setIsMailValidated(true);
        $user->setResetPasswordHash(null);

        $this->om->persist($user);
        $this->om->flush();
    }

    public function setInitDate(User $user): void
    {
        if (null === $user->getInitDate()) {
            $accountDuration = $this->platformConfigHandler->getParameter('account_duration');
            if (!empty($accountDuration)) {
                $expirationDate = new \DateTime();
                $expirationDate->add(new \DateInterval('P'.$accountDuration.'D'));

                $user->setInitDate(new \DateTime());
                $user->setExpirationDate($expirationDate);

                $this->om->persist($user);
                $this->om->flush();
            }
        }
    }

    public function initializePassword(User $user): void
    {
        $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
        $user->setResetPasswordHash($password);

        $this->om->persist($user);
        $this->om->flush();
    }

    public function enable(User $user): User
    {
        if (!$user->isEnabled()) {
            $user->enable();

            $this->om->persist($user);
            $this->om->flush();

            $this->dispatcher->dispatch(SecurityEvents::USER_ENABLE, UserEnableEvent::class, [$user]);
        }

        return $user;
    }

    public function disable(User $user): User
    {
        if ($user->isEnabled()) {
            $user->disable();

            $this->om->persist($user);
            $this->om->flush();

            $this->dispatcher->dispatch(SecurityEvents::USER_DISABLE, UserDisableEvent::class, [$user]);
        }

        return $user;
    }

    public function disableInactive(\DateTimeInterface $lastActivity): void
    {
        $this->messageBus->dispatch(new DisableInactiveUsers($lastActivity));
    }

    public function getDefaultClarolineAdmin(): User
    {
        $user = $this->getUserByUsername('support@claroline.com');

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

        // hide this user for everyone
        $user->setTechnical(true);

        return $user;
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
}
