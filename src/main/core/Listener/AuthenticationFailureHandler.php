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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Messenger\Security\Message\AuthenticationFailureMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /** @var ObjectManager */
    private $objectManager;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var TranslatorInterface */
    private $translator;
    /** @var Security */
    private $security;

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function setMessageBus(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setSecurity(Security $security)
    {
        $this->security = $security;
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
        $user = $this->objectManager->getRepository(User::class)->findByName($username);

        if ($user) {
            $user = $user[0]->getId();
        }

        $this->messageBus->dispatch(new AuthenticationFailureMessage(
            $user ?: null,
            $user ?: null,
            'event.security.authentication_failure',
            $this->translator->trans('authenticationFailure', ['username' => $username, 'message' => $message], 'security')
        ));
    }
}
