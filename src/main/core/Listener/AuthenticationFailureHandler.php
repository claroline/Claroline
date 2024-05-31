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

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    use RequestDecoderTrait;

    private EventDispatcherInterface $dispatcher;
    private ObjectManager $objectManager;

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function setObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $authData = $this->decodeRequest($request);

        $this->dispatchAuthenticationFailureEvent($authData['username'] ?? '', $exception->getMessage());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($exception->getMessage(), 401);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    private function dispatchAuthenticationFailureEvent(string $username, string $message): void
    {
        try {
            $user = $this->objectManager->getRepository(User::class)->loadUserByUsername($username);
        } catch (\Exception $e) {
            $user = $username;
        }

        $failureEvent = new AuthenticationFailureEvent($user, $message);
        $this->dispatcher->dispatch($failureEvent, SecurityEvents::AUTHENTICATION_FAILURE);
    }
}
