<?php

namespace Claroline\AuthenticationBundle\Messenger\Middleware;

use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Will read the AuthenticationStamp to know which user as called the messenger in order to refresh its token.
 * Without the AuthenticationStamp, the app will automatically populate the token with
 * the default claroline admin like in console command (it uses the same event).
 *
 * ATTENTION : Messenger gets a fresh ObjectManager, so the user stored in token is not known and will throw errors
 * if used in entities (most likely when setting the creator of new entities).
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Authenticator */
    private $authenticator;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        UserRepository $userRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var AuthenticationStamp|null $authenticationStamp */
        $authenticationStamp = $envelope->last(AuthenticationStamp::class);
        if ($authenticationStamp && $authenticationStamp->getUserId()) {
            $user = $this->userRepository->find($authenticationStamp->getUserId());
            if ($user) {
                $this->authenticator->createToken($user);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
