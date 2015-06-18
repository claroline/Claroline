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

use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Manager\MailManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * Authentication/login controller.
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
     *     "request"        = @DI\Inject("request"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "encoderFactory" = @DI\Inject("security.encoder_factory"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "authenticator"  = @DI\Inject("claroline.authenticator"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager"),
     *     "router"         = @DI\Inject("router"),
     *     "ch"             = @DI\Inject("claroline.config.platform_config_handler"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        Request $request,
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
    )
    {
        $this->request = $request;
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
     *     "/login",
     *     name="claro_security_login",
     *     options={"expose"=true}
     * )
     * @Template()
     *
     * Standard Symfony form login controller.
     *
     * @see http://symfony.com/doc/current/book/security.html#using-a-traditional-login-form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $lastUsername = $this->request->getSession()->get(SecurityContext::LAST_USERNAME);
        $user = $this->userManager->getUserByUsername($lastUsername);
        $selfRegistrationAllowed = $this->ch->getParameter('allow_self_registration');
        $showRegisterButton = $this->ch->getParameter('register_button_at_login');

        if ($user && !$user->isAccountNonExpired()) {
            return array(
                'last_username' => $lastUsername,
                'error' => false,
                'is_expired' => true,
                'selfRegistrationAllowed' => $selfRegistrationAllowed
            );
        }

        if ($this->request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $lastUsername,
            'error' => $error,
            'is_expired' => false,
            'selfRegistrationAllowed' => $selfRegistrationAllowed,
            'showRegisterButton' => $showRegisterButton
        );
    }

    /**
     * @Route(
     *     "/reset",
     *     name="claro_security_forgot_password",
     *     options={"expose"=true}
     * )
     * @Template("ClarolineCoreBundle:Authentication:forgotPassword.html.twig")
     */
    public function forgotPasswordAction()
    {
        if ($this->mailManager->isMailerAvailable()) {
            $form = $this->formFactory->create(FormFactory::TYPE_USER_EMAIL, array());

            return array('form' => $form->createView());
        }

        return array(
            'error' =>
                $this->translator->trans('mail_not_available', array(), 'platform')
                . ' '
                . $this->translator->trans('mail_config_problem', array(), 'platform')
        );
    }

    /**
     * @Route(
     *     "/passwords/reset",
     *     name="claro_security_initialize_password",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "ids"}
     * )
     */
    public function passwordInitializationAction(array $users)
    {
        foreach ($users as $user) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000) . $user->getUsername() . $user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->om->flush();
            $this->mailManager->sendForgotPassword($user);
        }

        return new Response(204);
    }

    /**
     * @Route(
     *     "/sendmail",
     *     name="claro_security_send_token",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @Template("ClarolineCoreBundle:Authentication:forgotPassword.html.twig")
     */
    public function sendEmailAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_USER_EMAIL, array(), null);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->userManager->getUserbyEmail($data['mail']);

            if (!empty($user)) {
                $user->setHashTime(time());
                $password = sha1(rand(1000, 10000) . $user->getUsername() . $user->getSalt());
                $user->setResetPasswordHash($password);
                $this->om->persist($user);
                $this->om->flush();

                if ($this->mailManager->sendForgotPassword($user)) {
                    return array(
                        'user' => $user,
                        'form' => $form->createView()
                    );
                }

                return array(
                    'error' => $this->translator->trans('mail_config_problem', array(), 'platform'),
                    'form' => $form->createView()
                );
            }

            return array(
                'error' => $this->translator->trans('mail_not_exist', array(), 'platform'),
                'form' => $form->createView()
            );
        }

        return array(
            'error' => $this->translator->trans('wrong_captcha', array(), 'platform'),
            'form' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/newpassword/{hash}/",
     *     name="claro_security_reset_password",
     *     options={"expose"=true}
     * )
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
     */
    public function resetPasswordAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);

        if (empty($user)) {
            return array(
                'error' => $this->translator->trans('url_invalid', array(), 'platform'),
            );
        }

        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array(), $user);
        $currentTime = time();

        // the link is valid for 24h
        if ($currentTime - (3600 * 24) < $user->getHashTime()) {
            return array(
                'hash' => $hash,
                'form' => $form->createView()
            );
        }

        return array('error' => $this->translator->trans('link_outdated', array(), 'platform'));
    }

    /**
     * @Route(
     *     "/validatepassword/{hash}",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
     */
    public function newPasswordAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);
        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array(), $user);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $plainPassword = $data->getPlainPassword();
            $user->setPlainPassword($plainPassword);
            $this->om->persist($user);
            $this->om->flush();
            $this->request->getSession()
                ->getFlashBag()
                ->add('warning', $this->translator->trans('password_ok', array(), 'platform'));

            return new RedirectResponse($this->router->generate('claro_security_login'));
        }

        return array(
            'hash' => $hash,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/authenticate.{format}")
     * @Method("POST")
     */
    public function postAuthenticationAction($format)
    {
        $formats = array('json', 'xml');

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
        $content = ($status === 403) ?
            array('message' => $this->translator->trans('login_failure', array(), 'platform')) :
            array();

        return $format === 'json' ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }

    //not routed...
    public function renderExternalAuthenticatonButtonAction()
    {
        $event = $this->dispatcher->dispatch('render_external_authentication_button', 'RenderAuthenticationButton');

        $eventContent = $event->getContent();
        if (!empty($eventContent)) {
            $eventContent = '<div class="external_authentication"><hr>' . $eventContent . '</div>';
        }

        return new Response($eventContent);
    }
}
