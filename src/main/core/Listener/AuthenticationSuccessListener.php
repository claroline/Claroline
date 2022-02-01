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
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AuthenticationBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
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
    /** @var PlatformManager */
    private $platformManager;
    /** @var UserManager */
    private $userManager;
    /** @var ToolManager */
    private $toolManager;
    /** @var ConnectionMessageManager */
    private $messageManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer,
        RoutingHelper $routingHelper,
        PlatformManager $platformManager,
        UserManager $userManager,
        ToolManager $toolManager,
        ConnectionMessageManager $messageManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->routingHelper = $routingHelper;
        $this->platformManager = $platformManager;
        $this->userManager = $userManager;
        $this->toolManager = $toolManager;
        $this->messageManager = $messageManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User $user */
        $user = $token->getUser();

        $this->userManager->setInitDate($user);

        if ($user->getLocale()) {
            $request->setLocale($user->getLocale());
        }

        $this->eventDispatcher->dispatch(SecurityEvents::USER_LOGIN, UserLoginEvent::class, [$user]);

        $redirect = $this->getRedirection($request);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'user' => $this->serializer->serialize($user),
                'administration' => !empty($this->toolManager->getAdminToolsByRoles($token->getRoleNames())),
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
            case 'last':
                // use the fragment sent by the ui to redirect user
                $redirectPath = $request->getSession()->get('redirectPath');
                if (!empty($redirectPath)) {
                    $redirectUrl = $this->routingHelper->indexUrl().$redirectPath;
                } else {
                    $redirectUrl = filter_var($request->headers->get('referer'), FILTER_SANITIZE_URL);
                }
                break;
            case 'desktop':
            default:
                $redirectUrl = $this->routingHelper->desktopPath();
                break;
        }

        return new RedirectResponse($redirectUrl);
    }

    private function getRedirection(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $redirect = $this->config->getParameter('authentication.redirect_after_login_option');
        $referer = filter_var($request->headers->get('referer'), FILTER_SANITIZE_URL);
        $redirectPath = $request->getSession()->get('redirectPath');
        if (PlatformDefaults::REDIRECT_OPTIONS['LAST'] === $redirect
            && ($redirectPath || ($referer && 0 === strpos($referer, $this->platformManager->getUrl())))) {
            // only redirect to previous url if it's part of the claroline platform or the ui has sent us a path to redirect to
            return [
                'type' => 'last',
            ];
        } elseif (
            PlatformDefaults::REDIRECT_OPTIONS['WORKSPACE_TAG'] === $redirect
            && $this->config->getParameter('workspace.default_tag')
        ) {
            /** @var GenericDataEvent $event */
            $event = $this->eventDispatcher->dispatch(
                'claroline_retrieve_user_workspaces_by_tag',
                GenericDataEvent::class,
                [
                    [
                        'tag' => $this->config->getParameter('workspace.default_tag'),
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
            PlatformDefaults::REDIRECT_OPTIONS['URL'] === $redirect
            && $this->config->getParameter('authentication.redirect_after_login_url')
        ) {
            return [
                'type' => 'url',
                'data' => $this->config->getParameter('authentication.redirect_after_login_url'),
            ];
        }

        return [
            'type' => 'desktop',
        ];
    }
}
