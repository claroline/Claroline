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
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * @DI\Service("claroline.security.authentication.success_handler")
 */
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

    /** @var UserManager */
    private $userManager;

    /** @var ConnectionMessageManager */
    private $messageManager;

    /**
     * SecurityController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "config"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer"      = @DI\Inject("claroline.api.serializer"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
     *     "messageManager"  = @DI\Inject("claroline.manager.connection_message_manager")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $config
     * @param StrictDispatcher             $eventDispatcher
     * @param SerializerProvider           $serializer
     * @param UserManager                  $userManager
     * @param ConnectionMessageManager     $messageManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer,
        UserManager $userManager,
        ConnectionMessageManager $messageManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->userManager = $userManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $this->userManager->logUser($user);

        if ($user->getLocale()) {
            $request->setLocale($user->getLocale());
        }

        return new JsonResponse([
            'user' => $this->serializer->serialize($user),
            'redirect' => $this->getRedirection(),
            'messages' => $this->messageManager->getConnectionMessagesByUser($user),
        ]);
    }

    private function getRedirection()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($this->config->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['LAST'])) {
            return [
                'type' => 'last',
            ];
        } elseif (
            $this->config->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['WORKSPACE_TAG'])
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
                    'data' => $this->serializer->serialize($workspaces[0], Options::SERIALIZE_MINIMAL),
                ];
            }
        } elseif (
            $this->config->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['URL'])
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
