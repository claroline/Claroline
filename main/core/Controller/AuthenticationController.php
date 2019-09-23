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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Authentication/login controller.
 *
 * @DI\Tag("security.secure_service")
 */
class AuthenticationController
{
    private $request;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailManager;
    private $translator;
    private $formFactory;
    private $authenticator;
    private $router;
    private $ch;
    private $dispatcher;

    /**
     * @DI\InjectParams({
     *     "request"        = @DI\Inject("request_stack"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "encoderFactory" = @DI\Inject("security.encoder_factory"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "authenticator"  = @DI\Inject("claroline.authenticator"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager"),
     *     "router"         = @DI\Inject("router"),
     *     "ch"             = @DI\Inject("claroline.config.platform_config_handler"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        RequestStack $request,
        UserManager $userManager,
        EncoderFactory $encoderFactory,
        ObjectManager $om,
        TranslatorInterface $translator,
        FormFactory $formFactory,
        Authenticator $authenticator,
        MailManager $mailManager,
        RouterInterface $router,
        PlatformConfigurationHandler $ch,
        StrictDispatcher $dispatcher
    ) {
        $this->request = $request->getMasterRequest();
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticator = $authenticator;
        $this->mailManager = $mailManager;
        $this->router = $router;
        $this->ch = $ch;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @EXT\Route(
     *     "/sendmail",
     *     name="claro_security_send_token",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function sendEmailAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->om->getRepository(User::class)->findOneByEmail($data['email']);

        if ($user) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->om->flush();

            if ($this->mailManager->sendForgotPassword($user)) {
                return new JsonResponse('password_reset_send', 200);
            }

            return new JsonResponse('mail_config_issue', 500);
        }

        $error = [
          'error' => ['email' => 'email_not_exist'],
        ];

        return new JsonResponse($error, 500);
    }

    /**
     * @EXT\Route(
     *     "/validatepassword",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function newPasswordAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userManager->getByResetPasswordHash($data['hash']);

        if (!$user) {
            return new JsonResponse('hash_invalid', 500);
        }

        if (null === $data['password'] && '' === trim($data['password'])) {
            $error = [
              'error' => ['password' => 'password_invalid'],
            ];

            return new JsonResponse($error, 400);
        }

        if ($data['password'] !== $data['confirm']) {
            $error = [
              'error' => ['password' => 'password_value_missmatch'],
            ];

            return new JsonResponse($error, 400);
        }

        $user->setPlainPassword($data['password']);
        $this->userManager->activateUser($user);
        $this->om->persist($user);
        $this->om->flush();

        return new JsonResponse(null, 201);
    }

    /**
     * @EXT\Route(
     *     "/validate/email/{hash}",
     *     name="claro_security_validate_email",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:authentication:reset_password.html.twig")
     */
    public function validateEmailAction($hash)
    {
        $this->userManager->validateEmailHash($hash);

        $this->request->getSession()
            ->getFlashBag()
            ->add('success', $this->translator->trans('email_validated', [], 'platform'));

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @EXT\Route(
     *     "/send/email/validation/{hash}",
     *     name="claro_security_validate_email_send",
     *     options={"expose"=true}
     * )
     */
    public function sendEmailValidationAction($hash)
    {
        $this->mailManager->sendValidateEmail($hash);
        $user = $this->userManager->getByEmailValidationHash($hash);
        $this->request->getSession()
            ->getFlashBag()
            ->add('success', $this->translator->trans('email_sent', ['%email%' => $user->getEmail()], 'platform'));

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @EXT\Route(
     *     "/hide/email/validation",
     *     name="claro_security_validate_email_hide",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function hideEmailConfirmationAction(User $user)
    {
        $this->userManager->hideEmailValidation($user);

        return new JsonResponse('success');
    }
}
