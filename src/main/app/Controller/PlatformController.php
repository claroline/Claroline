<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Manager\ClientManager;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * PlatformController is in charge of the rendering of the Claroline web UI.
 */
class PlatformController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Environment $templating,
        private readonly PlatformConfigurationHandler $configHandler,
        private readonly LocaleManager $localeManager,
        private readonly SecurityManager $securityManager,
        private readonly ContextProvider $contextProvider,
        private readonly SerializerProvider $serializer,
        private readonly ClientManager $clientManager
    ) {
    }

    /**
     * Renders the Claroline web application.
     */
    #[Route(path: '/', name: 'claro_index')]
    public function indexAction(Request $request): Response
    {
        $currentUser = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
        }

        $userPreferences = $this->clientManager->getUserPreferences($currentUser);

        return new Response(
            $this->templating->render('@ClarolineApp/index.html.twig', [
                'baseUrl' => $this->clientManager->getBaseUrl(),
                'parameters' => array_merge($this->clientManager->getParameters(), $userPreferences), // for retro-compatibility
                // 'userPreferences' => $this->clientManager->getUserPreferences($currentUser),

                'currentUser' => $currentUser ? $this->serializer->serialize(
                    $currentUser, [Options::SERIALIZE_FACET] // TODO : we should only get the minimal representation of user here
                ) : null,
                'impersonated' => $this->securityManager->isImpersonated(),
                'contexts' => $this->contextProvider->getAvailableContexts(),
                'contextFavorites' => $this->contextProvider->getFavoriteContexts(),
                'currentOrganization' => $currentUser ? $this->serializer->serialize($currentUser->getMainOrganization(), [Options::SERIALIZE_MINIMAL]) : null,
                'availableOrganizations' => $currentUser ? array_map(function (Organization $organization) {
                    return $this->serializer->serialize($organization, [Options::SERIALIZE_MINIMAL]);
                }, $currentUser->getOrganizations()) : [],

                'client' => [
                    'ip' => $request->getClientIp(),
                    'forwarded' => $request->headers->get('X-Forwarded-For'), // I can only get trusted proxies if I use symfony getClientIps()
                ],
                'footer' => [
                    'content' => $this->configHandler->getParameter('footer.content'),
                    'display' => [
                        'show' => $this->configHandler->getParameter('footer.show'),
                        'locale' => $this->configHandler->getParameter('footer.show_locale'),
                        'help' => $this->configHandler->getParameter('footer.show_help'),
                        // 'termsOfService' => $this->privacyManager->getTosEnabled($request->getLocale()),
                    ],
                ],

                // assets injected from plugins
                'javascripts' => $this->clientManager->getJavascripts(),
                'stylesheets' => $this->clientManager->getStylesheets(),
            ])
        );
    }

    /**
     * Change locale.
     */
    #[Route(path: '/locale/{locale}', name: 'claroline_locale_change')]
    public function changeLocaleAction(Request $request, string $locale): RedirectResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $this->localeManager->setUserLocale($locale);
        }

        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        return new RedirectResponse(
            $request->headers->get('referer')
        );
    }
}
