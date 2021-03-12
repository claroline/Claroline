<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var UserRepository */
    private $userRepository;

    public function setDispatcher(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setRepository(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $this->dispatchAuthenticationFailureEvent(json_decode($request->getContent(), true)['username'] ?? '', $exception->getMessage());

            return new JsonResponse($exception->getMessage(), 422);
        }

        $this->dispatchAuthenticationFailureEvent(json_decode($request->getContent(), true)['username'] ?? '', $exception->getMessage());

        return parent::onAuthenticationFailure($request, $exception);
    }

    private function dispatchAuthenticationFailureEvent(string $username, string $message): void
    {
        $this->dispatcher->dispatch(
            SecurityEvents::AUTHENTICATION_FAILURE,
            AuthenticationFailureEvent::class,
            [
                $this->userRepository->findUserByNameOnFailure($username) ?? $username,
                $message,
            ]
        );
    }
}
