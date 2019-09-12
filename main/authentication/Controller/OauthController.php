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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @EXT\Route("/oauth")
 */
class OauthController extends AbstractCrudController
{
    /** @var RouterInterface */
    private $router;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var OauthManager */
    private $oauthManager;

    /**
     * OauthController constructor.
     *
     * @DI\InjectParams({
     *     "router"        = @DI\Inject("router"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "oauthManager"  = @DI\Inject("claroline.oauth.manager")
     * })
     *
     * @param RouterInterface              $router
     * @param PlatformConfigurationHandler $configHandler
     * @param OauthManager                 $oauthManager
     */
    public function __construct(
        RouterInterface $router,
        PlatformConfigurationHandler $configHandler,
        OauthManager $oauthManager
    ) {
        $this->router = $router;
        $this->configHandler = $configHandler;
        $this->oauthManager = $oauthManager;
    }

    public function getClass()
    {
        return OauthUser::class;
    }

    public function getName()
    {
        return 'oauth';
    }

    /**
     * @EXT\Route("/check_connection", name="claro_oauth_check_connexion")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function checkConnexionAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $user = $session->get('claroline.oauth.user');

        if (null !== $service && null !== $user) {
            return new RedirectResponse($this->router->generate('claro_index').'#/external/'.$service['name']);
        }

        $session->remove('claroline.oauth.resource_owner');
        $session->remove('claroline.oauth.user');

        return new RedirectResponse($this->router->generate('claro_index'));
    }

    /**
     * @EXT\Route("/link_account/{service}/{username}", name="claro_oauth_link_account")
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param string  $username
     *
     * @return JsonResponse
     */
    public function linkAccountAction(Request $request, $username)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');

        return $this->oauthManager->linkAccount($request, $service, $username);
    }

    /**
     * @EXT\Route("/link_account_mail", name="claro_oauth_link_account_mail")
     * @EXT\Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function linkAccountByMailAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $username = $session->get('claroline.oauth.user')['email'];

        return $this->oauthManager->linkAccount($request, $service, $username);
    }
}
