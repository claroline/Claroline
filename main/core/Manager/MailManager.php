<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Manager\CacheManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Mailing\Mailer;
use Claroline\CoreBundle\Library\Mailing\Message;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailManager
{
    /** @var CacheManager */
    private $cacheManager;

    /** @var ContainerInterface */
    private $container;

    /** @var Mailer */
    private $mailer;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var TemplateManager */
    private $templateManager;

    /** @var TwigEngine */
    private $templating;

    private $parameters;

    /**
     * @param CacheManager          $cacheManager
     * @param ContainerInterface    $container
     * @param Mailer                $mailer
     * @param ParametersSerializer  $parametersSerializer
     * @param UrlGeneratorInterface $router
     * @param TemplateManager       $templateManager
     * @param TwigEngine            $templating
     */
    public function __construct(
        CacheManager $cacheManager,
        ContainerInterface $container,
        Mailer $mailer,
        ParametersSerializer $parametersSerializer,
        UrlGeneratorInterface $router,
        TemplateManager $templateManager,
        TwigEngine $templating
    ) {
        $this->cacheManager = $cacheManager;
        $this->container = $container;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templateManager = $templateManager;
        $this->templating = $templating;
        $this->serializer = $parametersSerializer;
    }

    /**
     * @return bool
     */
    public function isMailerAvailable()
    {
        return $this->container->get('claroline.config.platform_config_handler')->getParameter('mailer.enabled');
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return bool
     */
    public function sendForgotPassword(User $user)
    {
        $this->container->get('claroline.manager.user_manager')->initializePassword($user);
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate(
            'claro_security_reset_password',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password_reset_link' => $link,
        ];
        $locale = $this->container->get('claroline.manager.locale_manager')->getLocale($user);
        $subject = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('forgotten_password', $placeholders, $locale);

        return $this->send($subject, $body, [$user], null, [], true);
    }

    public function sendInitPassword(User $user)
    {
        $this->container->get('claroline.manager.user_manager')->initializePassword($user);
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate(
            'claro_security_reset_password',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $locale = $this->container->get('claroline.manager.locale_manager')->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password_initialization_link' => $link,
        ];
        $subject = $this->templateManager->getTemplate('password_initialization', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('password_initialization', $placeholders, $locale);

        return $this->send($subject, $body, [$user], null, [], true);
    }

    public function sendEnableAccountMessage(User $user)
    {
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate(
            'claro_security_activate_user',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $locale = $this->container->get('claroline.manager.locale_manager')->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'user_activation_link' => $link,
        ];
        $subject = $this->templateManager->getTemplate('user_activation', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('user_activation', $placeholders, $locale);

        return $this->send($subject, $body, [$user], null, [], true);
    }

    public function sendValidateEmail($hash)
    {
        $user = $this->container->get('claroline.manager.user_manager')->getByEmailValidationHash($hash);
        $url = $this->router->generate(
            'claro_security_validate_email',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $locale = $this->container->get('claroline.manager.locale_manager')->getLocale($user);
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'validation_mail' => $url,
        ];
        $subject = $this->templateManager->getTemplate('claro_mail_validation', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('claro_mail_validation', $placeholders, $locale);

        $this->send($subject, $body, [$user], null, [], true);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function sendCreationMessage(User $user)
    {
        $locale = $this->container->get('claroline.manager.locale_manager')->getLocale($user);
        $url = $this->router->generate(
            'claro_security_validate_email',
            ['hash' => $user->getEmailValidationHash()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword(),
            'validation_mail' => $url,
        ];
        $subject = $this->templateManager->getTemplate('claro_mail_registration', $placeholders, $locale, 'title');
        $body = $this->templateManager->getTemplate('claro_mail_registration', $placeholders, $locale);

        return $this->send($subject, $body, [$user], null, [], true);
    }

    /**
     * @param string $subject
     * @param string $body
     * @param User[] $users
     * @param User   $from
     * @param array  $extra
     * @param bool   $force
     * @param string $replyToMail
     *
     * @return bool
     */
    public function send($subject, $body, array $users, $from = null, array $extra = [], $force = false, $replyToMail = null)
    {
        $parameters = $this->serializer->serialize([Options::SERIALIZE_MINIMAL]);
        if (0 === count($users) && (!isset($extra['to']) || 0 === count($extra['to']))) {
            //obviously, if we're not going to send anything to anyone, it's better to stop
            return false;
        }

        if ($this->isMailerAvailable()) {
            $to = [];

            $fromEmail = $parameters['mailer']['from'];
            $locale = 1 === count($users) ? $users[0]->getLocale() : $parameters['locales']['default'];

            if (!$locale) {
                $locale = $parameters['locales']['default'];
            }

            $body = $this->templateManager->getTemplate('claro_mail_layout', ['content' => $body], $locale);
            $body = str_replace('%platform_name%', $parameters['display']['name'], $body);

            if ($from) {
                $body = str_replace('%first_name%', $from->getFirstName(), $body);
                $body = str_replace('%last_name%', $from->getLastName(), $body);
            } else {
                $body = str_replace('%first_name%', $parameters['display']['name'], $body);
                $body = str_replace('%last_name%', '', $body);
            }

            foreach ($users as $user) {
                $email = $user->getEmail();

                if ($user->isMailValidated() || $force) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $to[] = $email;
                    }
                }
            }

            if (isset($extra['to'])) {
                foreach ($extra['to'] as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $to[] = $email;
                    }
                }
            }

            $message = new Message();
            $message->subject($subject);
            $message->from($fromEmail);
            $message->body($body);

            (null !== $from && filter_var($from->getEmail(), FILTER_VALIDATE_EMAIL)) ?
                $message->replyTo($from->getEmail()) :
                $message->replyTo($replyToMail);

            if (count($to) > 1) {
                $message->bcc($to);
            } else {
                $message->to($to);
            }

            if (isset($extra['attachment'])) {
                $message->attach($extra['attachment'], 'application/octet-stream');
            }

            return $this->mailer->send($message) ? true : false;
        }

        return false;
    }

    public function getMailerFrom()
    {
        $parameters = $this->serializer->serialize([Options::SERIALIZE_MINIMAL]);
        if ($from = $parameters['mailer']['from']) {
            if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
                return $from;
            }
        }

        if ($parameters['internet']['domain_name'] && '' !== trim($parameters['internet']['domain_name'])) {
            return 'noreply@'.$parameters['internet']['domain_name'];
        }

        return $parameters['help']['support_email'];
    }
}
