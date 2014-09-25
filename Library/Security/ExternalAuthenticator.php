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

use Claroline\CoreBundle\Manager\AuthenticationManager;
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

    /**
     * @InjectParams({
     *     "encoderFactory"         = @Inject("security.encoder_factory"),
     *     "authenticationManager"  = @Inject("claroline.common.authentication_manager"),
     * })
     *
     */
    public function __construct(EncoderFactoryInterface $encoderFactory,AuthenticationManager $authenticationManager)
    {
        $this->encoderFactory = $encoderFactory;
        $this->authenticationManager = $authenticationManager;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException('Invalid username or password');
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordValid = $encoder->isPasswordValid(
            $user->getPassword(),
            $token->getCredentials(),
            $user->getSalt()
        );

        if ($user->getAuthentication() and $user->getAuthentication() !== '' && $token->getCredentials()) {
            if (!$this->authenticationManager->authenticate(
                $user->getAuthentication(), $user->getUsername(), $token->getCredentials()
            )) {
                throw new AuthenticationException('External connection failed', 100);
            }

            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        if ($passwordValid) {

            //throw new \Exception(var_dump($user->getAuthentication()));

            /*$currentHour = date('G');
            if ($currentHour < 10 || $currentHour > 16) {
                throw new AuthenticationException(
                    'You can only log in between 10 and 16!',
                    100
                );
            }*/

            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        throw new AuthenticationException('Invalid username or password');
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
}

