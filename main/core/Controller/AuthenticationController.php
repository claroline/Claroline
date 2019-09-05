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
use Claroline\CoreBundle\Form\EmailType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Security;
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
     * @Route(
     *     "/reset",
     *     name="claro_security_forgot_password",
     *     options={"expose"=true}
     * )
     * @Template("ClarolineCoreBundle:authentication:forgot_password.html.twig")
     */
    public function forgotPasswordAction()
    {
        if ($this->mailManager->isMailerAvailable()) {
            $form = $this->formFactory->create(EmailType::class);

            return ['form' => $form->createView()];
        }

        return [
            'error' => $this->translator->trans('mail_not_available', [], 'platform')
                .' '
                .$this->translator->trans('mail_config_problem', [], 'platform'),
        ];
    }

    /**
     * @Route(
     *     "/sendmail",
     *     name="claro_security_send_token",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @Template("ClarolineCoreBundle:authentication:forgot_password.html.twig")
     */
    public function sendEmailAction()
    {
        $form = $this->formFactory->create(EmailType::class);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->om->getRepository('ClarolineCoreBundle:User')->findOneByEmail($data['email']);

            if (!empty($user)) {
                $user->setHashTime(time());
                $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
                $user->setResetPasswordHash($password);
                $this->om->persist($user);
                $this->om->flush();

                if ($this->mailManager->sendForgotPassword($user)) {
                    return [
                        'user' => $user,
                        'form' => $form->createView(),
                    ];
                }

                return [
                    'error' => $this->translator->trans('mail_config_problem', [], 'platform'),
                    'form' => $form->createView(),
                ];
            }

            return [
                'error' => $this->translator->trans('mail_not_exist', [], 'platform'),
                'form' => $form->createView(),
            ];
        }

        return [
            'error' => $this->translator->trans('wrong_captcha', [], 'platform'),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *     "/newpassword/{hash}/",
     *     name="claro_security_reset_password",
     *     options={"expose"=true}
     * )
     *
     * @Template("ClarolineCoreBundle:authentication:reset_password.html.twig")
     */
    public function resetPasswordAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);

        if (empty($user)) {
            return [
                'error' => $this->translator->trans('url_invalid', [], 'platform'),
            ];
        }

        $form = $this->formFactory->create(ResetPasswordType::class, $user);
        $currentTime = time();

        // the link is valid for 24h
        if ($currentTime - (3600 * 24) < $user->getHashTime()) {
            return [
                'hash' => $hash,
                'form' => $form->createView(),
            ];
        }

        return ['error' => $this->translator->trans('link_outdated', [], 'platform')];
    }

    /**
     * @Route(
     *     "/validatepassword/{hash}",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:authentication:reset_password.html.twig")
     */
    public function newPasswordAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);
        $form = $this->formFactory->create(ResetPasswordType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $plainPassword = $data->getPlainPassword();
            $user->setPlainPassword($plainPassword);
            $this->userManager->activateUser($user);
            $this->om->persist($user);
            $this->om->flush();
            $this->request->getSession()
                ->getFlashBag()
                ->add('warning', $this->translator->trans('password_ok', [], 'platform'));

            return new RedirectResponse($this->router->generate('claro_security_login'));
        }

        return [
            'hash' => $hash,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *     "/validate/email/{hash}",
     *     name="claro_security_validate_email",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:authentication:reset_password.html.twig")
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
     * @Route(
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
     * @Route(
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

    /**
     * @Route("/authenticate.{format}")
     * @Method("POST")
     */
    public function postAuthenticationAction($format)
    {
        $formats = ['json', 'xml'];

        if (!in_array($format, $formats)) {
            return new Response(
                "The format {$format} is not supported (supported formats are 'json', 'xml'",
                400
            );
        }

        $request = $this->request;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $status = $this->authenticator->authenticate($username, $password) ? 200 : 403;
        $content = (403 === $status) ?
            ['message' => $this->translator->trans('login_failure', [], 'platform')] :
            [];

        return 'json' === $format ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }
}
