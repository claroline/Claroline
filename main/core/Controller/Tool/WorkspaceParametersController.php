<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $tokenStorage;
    private $authorization;
    private $translator;
    private $router;

    /**
     * @DI\InjectParams({
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "router"           = @DI\Inject("router"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorization
     * @param UrlGeneratorInterface         $router
     * @param TranslatorInterface           $translator
     * @param WorkspaceManager              $workspaceManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->router = $router;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate/anonymous",
     *     name="claro_workspace_subscription_url_generate_anonymous"
     * )
     * @EXT\Template("ClarolineCoreBundle:tool\workspace\parameters:generate_url_subscription_anonymous.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return array
     */
    public function anonymousSubscriptionAction(Workspace $workspace, Request $request)
    {
        $configHandler = $this->container->get('claroline.config.platform_config_handler');

        if (!$configHandler->getParameter('registration.self')) {
            throw new AccessDeniedException();
        }

        $profilerSerializer = $this->container->get('claroline.serializer.profile');
        $tosManager = $this->container->get('claroline.common.terms_of_service_manager');
        $allowWorkspace = $configHandler->getParameter('allow_workspace_at_registration');

        return [
            'workspace' => $workspace,
            'code' => $request->query->get('_code'),
            'facets' => $profilerSerializer->serialize([Options::REGISTRATION]),
            'termOfService' => $configHandler->getParameter('terms_of_service') ? $tosManager->getTermsOfService() : null,
            'options' => [
                'autoLog' => $configHandler->getParameter('auto_logging'),
                'localeLanguage' => $configHandler->getParameter('locale_language'),
                'defaultRole' => $configHandler->getParameter('default_role'),
                'redirectAfterLoginOption' => $configHandler->getParameter('redirect_after_login_option'),
                'redirectAfterLoginUrl' => $configHandler->getParameter('redirect_after_login_url'),
                'userNameRegex' => $configHandler->getParameter('username_regex'),
                'forceOrganizationCreation' => $configHandler->getParameter('force_organization_creation'),
                'allowWorkspace' => $allowWorkspace,
            ],
        ];
    }

    /**
     * @EXT\Route(
     *     "/user/subscribe/workspace/{workspace}",
     *     name="claro_workspace_subscription_url_generate_user",
     *     options={"expose"=true}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function userSubscriptionAction(Workspace $workspace, Request $request)
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()->getUser();

        // If user is admin or registration validation is disabled, subscribe user
        if ($this->authorization->isGranted('ROLE_ADMIN') || !$workspace->getRegistrationValidation()) {
            $code = $request->query->get('_code');
            $user->setCode($code);
            $this->workspaceManager->addUserAction($workspace, $user);

            return new RedirectResponse(
                $this->router->generate('claro_workspace_open', [
                    'workspaceId' => $workspace->getId(),
                ])
            );
        }
        // Otherwise add user to validation queue if not already there
        if (!$this->workspaceManager->isUserInValidationQueue($workspace, $user)) {
            $this->workspaceManager->addUserQueue($workspace, $user);
        }

        $flashBag = $request->getSession()->getFlashBag();
        $flashBag->set('warning', $this->translator->trans('workspace_awaiting_validation', [], 'platform'));

        return new RedirectResponse(
            $this->router->generate('claro_desktop_open')
        );
    }
}
