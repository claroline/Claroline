<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Entity\OauthUser;
use Claroline\AuthenticationBundle\Manager\OauthManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/oauth")
 */
class OauthController extends AbstractCrudController
{
    /** @var RouterInterface */
    private $router;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var OauthManager */
    private $oauthManager;

    public function __construct(
        RouterInterface $router,
        PlatformConfigurationHandler $configHandler,
        OauthManager $oauthManager
    ) {
        $this->router = $router;
        $this->configHandler = $configHandler;
        $this->oauthManager = $oauthManager;
    }

    public function getClass(): string
    {
        return OauthUser::class;
    }

    public function getName(): string
    {
        return 'oauth';
    }

    /**
     * @Route("/check_connection", name="claro_oauth_check_connexion")
     */
    public function checkConnectionAction(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        $service = $session->get('claroline.oauth.resource_owner');
        if (null !== $service) {
            // The user need to manually validate the link between the oauth provider and its claroline account
            // If he is not logged, he will need to log first.
            // If the platform allows it, he will be able to register and link its account.
            return new RedirectResponse($this->router->generate('claro_index').'#/external/'.$service['name']);
        }

        return new RedirectResponse($this->router->generate('claro_index').'#/desktop');
    }

    /**
     * @Route("/link_account/{service}/{username}", name="claro_oauth_link_account", methods={"POST"})
     */
    public function linkAccountAction(Request $request, string $username): JsonResponse
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');

        return $this->oauthManager->linkAccount($request, $service, $username);
    }
}
