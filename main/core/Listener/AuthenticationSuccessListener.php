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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var RoutingHelper */
    private $routingHelper;

    /** @var UserManager */
    private $userManager;

    /** @var ConnectionMessageManager */
    private $messageManager;

    /**
     * AuthenticationSuccessListener constructor.
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $config
     * @param StrictDispatcher             $eventDispatcher
     * @param SerializerProvider           $serializer
     * @param RoutingHelper                $routingHelper
     * @param UserManager                  $userManager
     * @param ConnectionMessageManager     $messageManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer,
        RoutingHelper $routingHelper,
        UserManager $userManager,
        ConnectionMessageManager $messageManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->routingHelper = $routingHelper;
        $this->userManager = $userManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $this->userManager->logUser($user);

        if ($user->getLocale()) {
            $request->setLocale($user->getLocale());
        }

        $redirect = $this->getRedirection();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'user' => $this->serializer->serialize($user),
                'redirect' => $redirect,
                'messages' => $this->messageManager->getConnectionMessagesByUser($user),
            ]);
        }

        switch ($redirect['type']) {
            case 'url':
                $redirectUrl = $redirect['data'];
                break;
            case 'workspace':
                $redirectUrl = $this->routingHelper->workspacePath($redirect['data']);
                break;
            case 'desktop':
            default:
                $redirectUrl = $this->routingHelper->desktopPath();
                break;
        }

        return new RedirectResponse($redirectUrl);
    }

    private function getRedirection()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $redirect = $this->config->getParameter('authentication.redirect_after_login_option');
        if (PlatformDefaults::$REDIRECT_OPTIONS['LAST'] === $redirect) {
            return [
                'type' => 'last',
            ];
        } elseif (
            PlatformDefaults::$REDIRECT_OPTIONS['WORKSPACE_TAG'] === $redirect
            && null !== $defaultWorkspaceTag = $this->config->getParameter('workspace.default_tag')
        ) {
            /** @var GenericDataEvent $event */
            $event = $this->eventDispatcher->dispatch(
                'claroline_retrieve_user_workspaces_by_tag',
                GenericDataEvent::class,
                [
                    [
                        'tag' => $defaultWorkspaceTag,
                        'user' => $user,
                        'ordered_by' => 'id',
                        'order' => 'ASC',
                        'type' => Role::WS_ROLE,
                    ],
                ]
            );
            $workspaces = $event->getResponse();

            if (is_array($workspaces) && count($workspaces) > 0) {
                return [
                    'type' => 'workspace',
                    'data' => $this->serializer->serialize($workspaces[0], [Options::SERIALIZE_MINIMAL]),
                ];
            }
        } elseif (
            PlatformDefaults::$REDIRECT_OPTIONS['URL'] === $redirect
            && null !== $url = $this->config->getParameter('redirect_after_login_url')
        ) {
            return [
                'type' => 'url',
                'data' => $url,
            ];
        }

        return [
            'type' => 'desktop',
        ];
    }
}
