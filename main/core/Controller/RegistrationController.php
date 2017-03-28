<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Claroline\CoreBundle\Manager\RegistrationManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationController extends Controller
{
    private $request;
    private $userManager;
    private $configHandler;
    private $validator;
    /** @var RegistrationManager */
    private $registrationManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "request"                = @DI\Inject("request"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "registrationManager"    = @DI\Inject("claroline.manager.registration_manager"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "validator"              = @DI\Inject("validator"),
     *     "translator"             = @DI\Inject("translator")
     * })
     *
     * @param Request                      $request
     * @param UserManager                  $userManager
     * @param PlatformConfigurationHandler $configHandler
     * @param ValidatorInterface           $validator
     * @param RegistrationManager          $registrationManager
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        Request $request,
        UserManager $userManager,
        PlatformConfigurationHandler $configHandler,
        ValidatorInterface $validator,
        RegistrationManager $registrationManager,
        TranslatorInterface $translator
    ) {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->configHandler = $configHandler;
        $this->validator = $validator;
        $this->registrationManager = $registrationManager;
        $this->translator = $translator;
    }
    /**
     * @Route(
     *     "/form",
     *     name="claro_registration_user_registration_form"
     * )
     *
     * @Template()
     *
     * Displays the user self-registration form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userRegistrationFormAction()
    {
        $this->checkAccess();
        $user = new User();
        $form = $this->registrationManager->getRegistrationForm($user);

        return ['form' => $form->createView()];
    }

    /**
     * @Route(
     *     "/create",
     *     name="claro_registration_register_user"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:userRegistrationForm.html.twig")
     *
     * Registers a new user and displays a flash message in case of success.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerUserAction()
    {
        $this->checkAccess();
        $user = new User();
        $form = $this->registrationManager->getRegistrationForm($user);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->registrationManager->registerNewUser($user, $form);

            $msg = $this->translator->trans('account_created', [], 'platform');
            $this->request->getSession()->getFlashBag()->add('success', $msg);

            if ($this->configHandler->getParameter('registration_mail_validation')) {
                $msg = $this->translator->trans('please_validate_your_account', [], 'platform');
                $this->request->getSession()->getFlashBag()->add('success', $msg);
            }

            if ($this->configHandler->getParameter('auto_logging_after_registration')) {
                return $this->registrationManager->loginUser($user, $this->request);
            }

            return $this->redirect($this->generateUrl('claro_security_login'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/new/user.{format}", name = "claro_register_user")
     * @Method({"POST"})
     */
    public function postUserRegistrationAction($format)
    {
        $formats = ['json', 'xml'];

        if (!in_array($format, $formats)) {
            return new Response(
                "The format {$format} is not supported (supported formats are 'json', 'xml')",
                400
            );
        }

        $status = 200;
        $content = [];

        if ($this->configHandler->getParameter('allow_self_registration')) {
            $request = $this->request;

            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setPlainPassword($request->request->get('password'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setMail($request->request->get('mail'));

            $errorList = $this->validator->validate($user);

            if (count($errorList) > 0) {
                $status = 422;
                foreach ($errorList as $error) {
                    $content[] = ['property' => $error->getPropertyPath(), 'message' => $error->getMessage()];
                }
            } else {
                $this->userManager->createUser($user);
            }
        } else {
            $status = 403;
        }

        return $format === 'json' ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }

    /**
     * @Route(
     *     "/activate/{hash}/",
     *     name="claro_security_activate_user",
     *     options={"expose"=true}
     * )
     */
    public function activateUserAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);

        if (!$user) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->translator->trans('link_outdated', [], 'platform')
            );

            return $this->redirect($this->generateUrl('claro_security_login'));
        }

        $this->userManager->activateUser($user);
        $this->userManager->logUser($user);

        return new RedirectResponse($this->generateUrl('claro_desktop_open'));
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return Respone
     */
    private function checkAccess()
    {
        $tokenStorage = $this->get('security.token_storage');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $isSelfRegistrationAllowed = $configHandler->getParameter('allow_self_registration');

        if (!$tokenStorage->getToken()->getUser() instanceof User && $isSelfRegistrationAllowed) {
            return;
        }

        throw new AccessDeniedHttpException();
    }
}
