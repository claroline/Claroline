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
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 *
 * @EXT\Route("/user/registration", options={"expose"=true})
 */
class RegistrationController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SessionInterface */
    private $session;
    /** @var TranslatorInterface */
    private $translator;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;

    private $parameters;

    /**
     * RegistrationController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"         = @DI\Inject("security.token_storage"),
     *     "session"              = @DI\Inject("session"),
     *     "translator"           = @DI\Inject("translator"),
     *     "profileSerializer"    = @DI\Inject("Claroline\CoreBundle\API\Serializer\User\ProfileSerializer"),
     *     "userManager"          = @DI\Inject("claroline.manager.user_manager"),
     *     "parametersSerializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param SessionInterface      $session
     * @param TranslatorInterface   $translator
     * @param ProfileSerializer     $profileSerializer
     * @param UserManager           $userManager
     * @param ParametersSerializer  $parametersSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        TranslatorInterface $translator,
        ProfileSerializer $profileSerializer,
        UserManager $userManager,
        ParametersSerializer $parametersSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->translator = $translator;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;

        $this->parameters = $parametersSerializer->serialize();
    }

    /**
     * Displays the user self-registration form.
     *
     * @EXT\Route("", name="claro_user_registration")
     * @EXT\Template("ClarolineCoreBundle:user:registration.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        $this->checkAccess();

        return [];
    }

    /**
     * @EXT\Route("/activate/{hash}", name="claro_security_activate_user")
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
     * Fetches data self-registration form.
     *
     * @EXT\Route("/fetch", name="claro_user_registration_data_fetch")
     *
     * @return JsonResponse
     */
    public function registrationDataFetchAction()
    {
        $this->checkAccess();

        return new JsonResponse([
            'facets' => $this->profileSerializer->serialize([Options::REGISTRATION]),
            'termOfService' => $this->parameters['tos']['text'] ? $this->parameters['tos']['text'] : null,
            'options' => [
                'autoLog' => $this->parameters['registration']['auto_logging'],
                'localeLanguage' => $this->parameters['locales']['default'],
                'defaultRole' => $this->parameters['registration']['default_role'],
                'userNameRegex' => $this->parameters['registration']['username_regex'],
                'forceOrganizationCreation' => $this->parameters['registration']['force_organization_creation'],
                'allowWorkspace' => $this->parameters['registration']['allow_workspace'],
            ],
        ]);
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws AccessDeniedException
     */
    private function checkAccess()
    {
        if (!$this->parameters['registration']['self'] || $this->tokenStorage->getToken()->getUser() instanceof User) {
            throw new AccessDeniedException();
        }
    }
}
