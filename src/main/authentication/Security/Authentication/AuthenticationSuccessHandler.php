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

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Manager\ClientManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Translation\LocaleSwitcher;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly SerializerProvider $serializer,
        private readonly RoutingHelper $routingHelper,
        private readonly PlatformManager $platformManager,
        private readonly ContextProvider $contextProvider,
        private readonly UserManager $userManager,
        private readonly ConnectionMessageManager $messageManager,
        private readonly ClientManager $clientManager
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User $user */
        $user = $token->getUser();

        $this->userManager->setInitDate($user);

        if ($user->getLocale() && $user->getLocale() !== $request->getLocale()) {
            $request->setLocale($user->getLocale());
            $request->getSession()->set('_locale', $user->getLocale());

            $this->localeSwitcher->setLocale($user->getLocale());
        }

        $loginEvent = new UserLoginEvent($user);
        $this->eventDispatcher->dispatch($loginEvent, SecurityEvents::USER_LOGIN);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'user' => $this->serializer->serialize($user, [SerializerInterface::SERIALIZE_MINIMAL]),
                'acceptedTerms' => $user->hasAcceptedTerms(),
                'config' => $this->clientManager->getUserPreferences($user), // For retro-compatibility. Should be in a new key userPreferences
                'messages' => $this->messageManager->getConnectionMessagesByUser($user),
                'contexts' => $this->contextProvider->getAvailableContexts(),
                'contextFavorites' => $this->contextProvider->getFavoriteContexts(),
                'currentOrganization' => $this->serializer->serialize($user->getMainOrganization()/* , [SerializerInterface::SERIALIZE_MINIMAL] */),
            ]);
        }

        return new RedirectResponse($this->getRedirectUrl($request));
    }

    private function getRedirectUrl(Request $request): string
    {
        // SSO has stored where to redirect in the session, or the ui has sent us a path to redirect to
        $redirectPath = $request->getSession()->get('redirectPath');
        if ($redirectPath) {
            return $this->routingHelper->indexUrl().$redirectPath;
        }

        // only redirect to previous url if it's part of the claroline platform
        $referer = filter_var($request->headers->get('referer'), FILTER_SANITIZE_URL);
        if ($referer && str_starts_with($referer, $this->platformManager->getUrl())) {
            return $referer;
        }

        return $this->routingHelper->desktopPath();
    }
}
