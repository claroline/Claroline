<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\User;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 *
 * @EXT\Route("/user/registration")
 */
class RegistrationController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SessionInterface */
    private $session;
    /** @var TranslatorInterface */
    private $translator;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;
    /** @var TermsOfServiceManager */
    private $tosManager;

    /**
     * RegistrationController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"      = @DI\Inject("security.token_storage"),
     *     "session"           = @DI\Inject("session"),
     *     "translator"        = @DI\Inject("translator"),
     *     "configHandler"     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "profileSerializer" = @DI\Inject("claroline.serializer.profile"),
     *     "userManager"       = @DI\Inject("claroline.manager.user_manager"),
     *     "tosManager"        = @DI\Inject("claroline.common.terms_of_service_manager")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param SessionInterface             $session
     * @param TranslatorInterface          $translator
     * @param PlatformConfigurationHandler $configHandler
     * @param ProfileSerializer            $profileSerializer
     * @param UserManager                  $userManager
     * @param TermsOfServiceManager        $tosManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $configHandler,
        ProfileSerializer $profileSerializer,
        UserManager $userManager,
        TermsOfServiceManager $tosManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->translator = $translator;
        $this->configHandler = $configHandler;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;
        $this->tosManager = $tosManager;
    }

    /**
     * Displays the user self-registration form.
     *
     * @EXT\Route("", name="claro_user_registration")
     * @EXT\Template("ClarolineCoreBundle:User:registration.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        $this->checkAccess();

        return [
            'facets' => $this->profileSerializer->serialize([Options::REGISTRATION]),
            'termOfService' => $this->configHandler->getParameter('terms_of_service') ?
                $this->tosManager->getTermsOfService() : null,
            'options' => [
                'autoLog' => $this->configHandler->getParameter('auto_logging'),
                'localeLanguage' => $this->configHandler->getParameter('locale_language'),
                'defaultRole' => $this->configHandler->getParameter('default_role'),
                'redirectAfterLoginOption' => $this->configHandler->getParameter('redirect_after_login_option'),
                'redirectAfterLoginUrl' => $this->configHandler->getParameter('redirect_after_login_url'),
                'userNameRegex' => $this->configHandler->getParameter('username_regex'),
                'forceOrganizationCreation' => $this->configHandler->getParameter('force_organization_creation'),
            ],
        ];
    }

    /**
     * @EXT\Route(
     *     "/activate/{hash}",
     *     name="claro_security_activate_user",
     *     options={"expose"=true}
     * )
     *
     * @param string $hash
     *
     * @return RedirectResponse
     *
     * @todo move this to the api
     */
    public function activateUserAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);
        if (!$user) {
            $this->session->getFlashBag()->add(
                'warning',
                $this->translator->trans('link_outdated', [], 'platform')
            );

            return new RedirectResponse($this->generateUrl('claro_security_login'));
        }

        $this->userManager->activateUser($user);
        $this->userManager->logUser($user);

        return new RedirectResponse($this->generateUrl('claro_desktop_open'));
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws AccessDeniedHttpException
     */
    private function checkAccess()
    {
        $isSelfRegistrationAllowed = $this->configHandler->getParameter('allow_self_registration');
        if (!$isSelfRegistrationAllowed || $this->tokenStorage->getToken()->getUser() instanceof User) {
            throw new AccessDeniedHttpException();
        }
    }
}
