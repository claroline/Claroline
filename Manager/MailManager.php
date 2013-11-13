<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.mail_manager")
 */
class MailManager
{
    private $router;
    private $mailer;
    private $templating;
    private $translator;
    private $container;

    /**
     * @DI\InjectParams({
     *     "router"         = @DI\Inject("router"),
     *     "mailer"         = @DI\Inject("mailer"),
     *     "templating"     = @Di\Inject("templating"),
     *     "container"      = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        \Swift_Mailer $mailer,
        TwigEngine $templating,
        UrlGeneratorInterface $router,
        Translator $translator,
        ContainerInterface $container
    )
    {
        $this->router = $router;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->container = $container;
    }
    public function isMailerAvailable()
    {
        try {
            $this->mailer->getTransport()->start();

            return true;
        } catch (\Swift_TransportException $e) {
            return false;
        }
    }

    public function sendForgotPassword($from, $sender, $hash)
    {
        $msg = $this->translator->trans('mail_click', array(), 'platform');
        $link = $this->container->get('request')->server->get('HTTP_ORIGIN') . $this->router->generate(
            'claro_security_reset_password',
            array('hash' => $hash)
        );

        $body = $this->templating->render('ClarolineCoreBundle:Authentication:emailForgotPassword.html.twig',array('message' => $msg, 'link' => $link));
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('reset_pwd', array(), 'platform'))
            ->setFrom($from)
            ->setTo($sender)
            ->setBody($body);

        if ($this->mailer->send($message)) {
            return true;
        }

        return false;
    }

    public function sendPlainPassword($from,$sender,$body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('create_new_user_account', array(), 'platform'))
            ->setFrom($from)
            ->setTo($sender)
            ->setBody($body);

        if ($this->mailer->send($message)) {
            return true;
        }

        return false;
    }
}

