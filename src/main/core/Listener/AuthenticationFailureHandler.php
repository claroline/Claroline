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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    use RequestDecoderTrait;

    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $objectManager;

    public function setDispatcher(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $authData = $this->decodeRequest($request);

        $this->dispatchAuthenticationFailureEvent($authData['username'] ?? '', $exception->getMessage());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($exception->getMessage(), 422);
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

        $this->dispatcher->dispatch(
            SecurityEvents::AUTHENTICATION_FAILURE,
            AuthenticationFailureEvent::class,
            [
                $user,
                $message,
            ]
        );
    }
}
