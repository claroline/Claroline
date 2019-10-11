<?php

namespace Claroline\CoreBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\ApiToken;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Authentication\Token\ApiToken as SecurityApiToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * Manages authentication of users with api tokens.
 */
class ApiTokenAuthenticator implements SimplePreAuthenticatorInterface
{
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function inject(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        //maybe filter here
        return true;
    }

    public function createToken(Request $request, $providerKey)
    {
        $session = $request->hasPreviousSession() ? $request->getSession() : null;
        $apiKey = $request->query->get('apitoken');
        //if we're in the application, use the regular token
        if ($apiKey) {
            // initialize a new token for the user
            return new PreAuthenticatedToken(
              'anon.',
              $apiKey,
              $providerKey
          );
        }

        if ($session) {
            $token = $session->get('_security_main');
            $token = unserialize($token);

            if ($token) {
                return $token;
            }
        }

        return new AnonymousToken('key', 'anon.', ['ROLE_ANONYMOUS']);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($token instanceof UsernamePasswordToken) {
            $username = $token->getUser();

            $user = $this->om->getRepository(User::class)->loadUserByUsername($username);

            return new UsernamePasswordToken(
                $user,
                null,
                'main',
                $user->getRoles()
            );
        }

        $apiKey = $token->getCredentials();

        if ($apiKey) {
            $user = $this->om->getRepository(ApiToken::class)->findOneByToken($apiKey)->getUser();

            if ($user) {
                return new SecurityApiToken(
                  $user,
                  $apiKey,
                  $providerKey,
                  $user->getRoles()
              );
            }
        }

        //I wish it didn't have to handle anonymous aswell
        return new AnonymousToken($providerKey, 'anon.', ['ROLE_ANONYMOUS']);
    }
}
