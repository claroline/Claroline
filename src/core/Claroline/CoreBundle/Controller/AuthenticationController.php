<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Form\Factory\FormFactory;
/**
 * Authentication/login controller.
 */
class AuthenticationController
{
    private $request;
    private $router;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailer;
    private $translator;
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "request"        = @DI\Inject("request"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "encoderFactory" = @DI\Inject("security.encoder_factory"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "mailer"         = @DI\Inject("mailer"),
     *     "router"         = @DI\Inject("router"),
     *     "translator"     = @DI\Inject("translator"),
     *     "formFactory"    = @DI\Inject("claroline.form.factory")
     * })
    */
    public function __construct(
        Request $request,
        UserManager $userManager,
        EncoderFactory $encoderFactory,
        ObjectManager $om,
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        Translator $translator,
        FormFactory $formFactory
    )
    {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
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
        if ($this->request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        $lastUsername = $this->request->getSession()->get(SecurityContext::LAST_USERNAME);

        return array(
            'last_username' => $lastUsername,
            'error' => $error
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
        $form = $this->formFactory->create(FormFactory::TYPE_USER_EMAIL, array(), null);

        return array('form' => $form->createView());

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
        }

        if (!empty($user)) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000) . $user->getUsername() . $user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->om->flush();
            $link = $this->request->server->get('HTTP_ORIGIN') . $this->router->generate(
                'claro_security_reset_password',
                array('hash' => $user->getResetPasswordHash())
            );
            $body = '<p><a href="' . $link . '"/>Click me</a></p>';

            $message = \Swift_Message::newInstance()
                ->setSubject('Reset Your Password')
                ->setFrom('noreply@claroline.net')
                ->setTo($data['mail'])
                ->setBody($body, 'text/html');
            $this->mailer->send($message);

            return array(
                'user' => $user,
                'form' => $form->createView()
                );
        }

        return array(
              'error' => $this->translator->trans('no_email', array(), 'platform'),
              'form' => $form->createView()
         );
    }
    /**
     * @Route(
     *     "/newpassword/{hash}/",
     *     name="claro_security_reset_password",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
    */
    public function resetPasswordAction($hash)
    {
        $user = $this->userManager->getResetPasswordHash($hash);

        if (empty($user)) {
            return array(
                'error' => $this->translator->trans('url_invalid', array(), 'platform'),
                );
        }

        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array($user->getId()), null);
        $currentTime = time();

        // the link is valid for 24h
        if ($currentTime - (3600 * 24) < $user->getHashTime()) {

            return array(
                'id' => $user->getId(),
                'form' => $form->createView()
                );
        }

        return array('error' => $this->translator->trans('link_outdated', array(), 'platform'));
    }

    /**
     * @Route(
     *     "/validatepassword",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
    */
    public function newPasswordAction()
    {

        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array(), null);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $user = $form->getData();
            $plainPassword = $user['plainPassword'];
            $id = $user['id'];
            $user = $this->userManager->getUserById($id);
            $user->setPlainPassword($plainPassword);
            $this->om->persist($user);
            $this->om->flush();

            return array(
                'message' => $this->translator->trans('password_ok', array(), 'platform'),
                'user' => $user
            );
        }

        return array(
            'error' => $this->translator->trans('password_missmatch', array(), 'platform')
        );
    }
 }
