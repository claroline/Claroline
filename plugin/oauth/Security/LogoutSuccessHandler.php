<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/6/16
 */

namespace Icap\OAuthBundle\Security;

use Icap\OAuthBundle\Security\Hwi\ResourceOwnerFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class LogoutSuccessHandler.
 *
 * @DI\Service("icap.oauth.logout_success_handler")
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    private $router;

    private $resourceOwnerFactory;

    /**
     * LogoutSuccessHandler constructor.
     *
     * @DI\InjectParams({
     *     "session"                = @DI\Inject("session"),
     *     "router"                 = @DI\Inject("router"),
     *     "resourceOwnerFactory"   = @DI\Inject("icap.oauth.hwi.resource_owner_factory")
     * })
     *
     * @param SessionInterface     $session
     * @param Router               $router
     * @param ResourceOwnerFactory $resourceOwnerFactory
     */
    public function __construct(SessionInterface $session, Router $router, ResourceOwnerFactory $resourceOwnerFactory)
    {
        $this->session = $session;
        $this->router = $router;
        $this->resourceOwnerFactory = $resourceOwnerFactory;
    }
    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        $resourceOwnerToken = $this->session->get('icap.oauth.resource_owner_token');
        $redirectUrl = $this->router->generate('claro_index', [], true);
        if (!empty($resourceOwnerToken)) {
            try {
                $resourceOwnerName = str_replace('_', '', ucwords($resourceOwnerToken['resourceOwnerName'], '_'));
                $resourceOwner = $this->resourceOwnerFactory->{'get'.$resourceOwnerName.'ResourceOwner'}();
                if ($resourceOwnerName === 'Office365' || $resourceOwnerName === 'WindowsLive') {
                    return $resourceOwner->logout($redirectUrl);
                }
                $resourceOwner->revokeToken($resourceOwnerToken['token']);
            } catch (AuthenticationException $e) {
                // Do nothing
            }
        }

        return new RedirectResponse($redirectUrl);
    }
}
