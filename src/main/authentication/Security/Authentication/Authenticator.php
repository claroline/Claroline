<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security\Authentication;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Allows to manually manage user authentication and token.
 */
class Authenticator
{
    /** @var string */
    private $secret;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var EncoderFactoryInterface */
    private $encodeFactory;
    /** @var AuthenticationSuccessListener */
    private $authenticationHandler;
    /** @var UserProviderInterface */
    private $userRepo;
    /** @var StrictDispatcher */
    private $eventDispatcher;

    public function __construct(
        string $secret,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        EncoderFactoryInterface $encodeFactory,
        AuthenticationSuccessListener $authenticationHandler,
        StrictDispatcher $eventDispatcher
    ) {
        $this->secret = $secret;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->encodeFactory = $encodeFactory;
        $this->authenticationHandler = $authenticationHandler;
        $this->eventDispatcher = $eventDispatcher;

        $this->userRepo = $om->getRepository(User::class);
    }

    public function authenticate($username, $password, $validatePassword = true): ?User
    {
        try {
            /** @var User $user */
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            return null;
        }

        $passwordValidated = !$validatePassword;
        if ($validatePassword) {
            $encoder = $this->encodeFactory->getEncoder($user);
            $encodedPass = $encoder->encodePassword($password, $user->getSalt());

            $passwordValidated = $user->getPassword() === $encodedPass;
        }

        if ($passwordValidated) {
            $this->createToken($user);

            $this->eventDispatcher->dispatch(SecurityEvents::USER_LOGIN, UserLoginEvent::class, [$user]);

            return $user;
        }

        return null;
    }

    /**
     * Checks if a user is the one stored in the token.
     */
    public function isAuthenticatedUser(User $user): bool
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            return true;
        }

        return false;
    }

    public function login(User $user, Request $request)
    {
        $token = $this->createToken($user);

        // manually call authentication success listener
        return $this->authenticationHandler->onAuthenticationSuccess($request, $token);
    }

    public function createAnonymousToken()
    {
        $token = new AnonymousToken($this->secret, 'anon.', [PlatformRoles::ANONYMOUS]);
        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function createAdminToken(User $user = null)
    {
        if (!empty($user)) {
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', [PlatformRoles::ADMIN]);
        } else {
            $token = new UsernamePasswordToken('admin', '', 'main', [PlatformRoles::ADMIN]);
        }

        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function createToken(UserInterface $user, array $customRoles = [])
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', !empty($customRoles) ? $customRoles : $user->getRoles());
        $this->tokenStorage->setToken($token);

        return $token;
    }

    public function cancelUserUsurpation(TokenInterface $token)
    {
        if ($token instanceof SwitchUserToken) {
            $user = $token->getOriginalToken()->getUser();
            $this->om->refresh($user);

            return $this->createToken($user);
        }

        return $token;
    }

    public function cancelUsurpation(TokenInterface $token)
    {
        $user = $token->getUser();
        $this->om->refresh($user);

        return $this->createToken($user);
    }
}
