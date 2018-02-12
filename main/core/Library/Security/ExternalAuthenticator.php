<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @Service()
 */
class ExternalAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoderFactory;
    private $authenticationManager;
    private $userManager;

    /**
     * @InjectParams({
     *     "encoderFactory"         = @Inject("security.encoder_factory"),
     *     "authenticationManager"  = @Inject("claroline.common.authentication_manager"),
     *     "userManager"            = @Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        AuthenticationManager $authenticationManager,
        UserManager $userManager
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->authenticationManager = $authenticationManager;
        $this->userManager = $userManager;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());

            return $this->authenticate($user, $token, $providerKey);
        } catch (UsernameNotFoundException $e) {
            return $this->getFromProviders($token, $providerKey);
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    private function getFromProviders(TokenInterface $token, $providerKey)
    {
        $drivers = $this->authenticationManager->getDrivers();

        foreach ($drivers as $driver) {
            $found = $this->authenticationManager->authenticate($driver, $token->getUsername(), $token->getCredentials());

            if ($found) {
                $data = $this->authenticationManager->findUser($driver, $token->getUsername());
                $user = new User();
                $user->setFirstName($data['first_name']);
                $user->setPlainPassword(uniqid());
                $user->setLastName($data['last_name']);
                $user->setUsername($data['username']);
                $user->setEmail($data['email']);
                $user->setAuthentication($driver);
                $user = $this->userManager->createUser($user, false);

                return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
            }
        }

        throw new AuthenticationException('Invalid username or password');
    }

    private function authenticate(User $user, TokenInterface $token, $providerKey)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordValid = $encoder->isPasswordValid(
            $user->getPassword(),
            $token->getCredentials(),
            $user->getSalt()
        );

        //do we want an external authentication
        if ($user->getAuthentication() && $user->getAuthentication() !== '' && $token->getCredentials()) {
            if (!$this->authenticationManager->authenticate(
                $user->getAuthentication(), $user->getUsername(), $token->getCredentials()
            )) {
                throw new AuthenticationException('External connection failed', 100);
            }

            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        //do we want a regular authentication
        if ($passwordValid) {
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        throw new AuthenticationException('Invalid username or password');
    }
}
