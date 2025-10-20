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

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ObjectManager $objectManager,
        private readonly RoutingHelper $routingHelper,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $authData = $this->decodeRequest($request);

        $this->dispatchAuthenticationFailureEvent($authData['username'] ?? '', $exception->getMessage());

        // don't directly display the exception message as it can contain sensitive information about the failure
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($exception->getMessageKey(), 401);
        }

        return new RedirectResponse($this->routingHelper->indexUrl().'#/login?error='.$exception->getMessageKey());
    }

    private function dispatchAuthenticationFailureEvent(string $username, string $message): void
    {
        try {
            $user = $this->objectManager->getRepository(User::class)->loadUserByIdentifier($username);
        } catch (\Exception $e) {
            $user = $username;
        }

        $failureEvent = new AuthenticationFailureEvent($user, $message);
        $this->dispatcher->dispatch($failureEvent, SecurityEvents::AUTHENTICATION_FAILURE);
    }
}
