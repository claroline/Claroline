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

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\CacheWarmerInterface;
use Claroline\CoreBundle\Event\RefreshCacheEvent;

/**
 * @DI\Service("claroline.manager.mail_manager")
 */
class MailManager
{
    private $router;
    private $mailer;
    private $translator;
    private $container;
    private $ch;
    private $cacheManager;
    private $contentManager;

    /**
     * @DI\InjectParams({
     *     "router"         = @DI\Inject("router"),
     *     "mailer"         = @DI\Inject("mailer"),
     *     "ch"             = @DI\Inject("claroline.config.platform_config_handler"),
     *     "container"      = @DI\Inject("service_container"),
     *     "cacheManager"   = @DI\Inject("claroline.manager.cache_manager"),
     *     "contentManager" = @DI\Inject("claroline.manager.content_manager")
     * })
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        Translator $translator,
        PlatformConfigurationHandler $ch,
        ContainerInterface $container,
        CacheManager $cacheManager,
        ContentManager $contentManager
    )
    {
        $this->router = $router;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->container = $container;
        $this->ch = $ch;
        $this->cacheManager = $cacheManager;
        $this->contentManager = $contentManager;
    }


    /**
     * @return boolean
     */
    public function isMailerAvailable()
    {
        return $this->cacheManager->getParameter('is_mailer_available');
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendForgotPassword(User $user)
    {
        $hash = $user->getResetPasswordHash();
        $msg = $this->translator->trans('mail_click', array(), 'platform');
        $link = $this->container->get('request')->server->get('HTTP_ORIGIN') . $this->router->generate(
            'claro_security_reset_password',
            array('hash' => $hash)
        );
        $body = "{$msg} {$link} {$user->getUsername()}";
        $subject = $this->translator->trans('reset_pwd', array(), 'platform');

        return $this->send($subject, $body, array($user));
    }

    /**
     * @param User $user
     *
     * @return boolean
     */
    public function sendCreationMessage(User $user)
    {
        $locale = $user->getLocale();
        $content = $this->contentManager->getTranslatedContent(array('type' => 'claro_mail_registration'));
        $displayedLocale = isset($content[$locale]) ? $locale: $this->ch->getParameter('locale_language');
        $body = $content[$displayedLocale]['content'];
        $subject =  $content[$displayedLocale]['title'];

        $body = str_replace('%username%', $user->getUsername(), $body);
        $body = str_replace('%password%', $user->getPlainPassword(), $body);
        $subject = str_replace('%platform_name%', $this->ch->getParameter('name'), $subject);

        return $this->send($subject, $body, array($user));
    }

    public function getMailInscription()
    {
        return $this->contentManager->getContent(array('type' => 'claro_mail_registration'));
    }

    public function getMailLayout()
    {
        return $this->contentManager->getContent(array('type' => 'claro_mail_layout'));
    }

    /**
     * @param string $subject
     * @param string $body
     * @param User[] $users
     * @param User   $from
     *
     * @return boolean
     */
    public function send($subject, $body, array $users, $from = null)
    {
        if ($this->isMailerAvailable()) {
            $layout = $this->contentManager->getTranslatedContent(array('type' => 'claro_mail_layout'));

            $from = ($from === null) ? $this->ch->getParameter('support_email'): $from->getMail();
            $to = array();

            $locale = count($users) === 1 ? $users[0]->getLocale(): $this->ch->getParameter('locale_language');

            if (!$locale) {
                $locale = $this->ch->getParameter('locale_language');
            }

            $usedLayout = $layout[$locale]['content'];
            $body = str_replace('%content%', $body, $usedLayout);
            $body = str_replace('%platform_name%', $this->ch->getParameter('name'), $body);

            foreach ($users as $user) {
                $to[] = $user->getMail();
            }

            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($from)
                ->setBody($body, 'text/html');

            if (count($to) > 1) {
                $message->setCc($to);
            } else {
                $message->setTo($to);
            }

            return $this->mailer->send($message) ? true: false;
        }

        return false;
    }

    /**
     * @param $translatedContents
     *
     * @return array
     */
    public function validateInscriptionMail(array $translatedContents)
    {
        $languages = array_keys($translatedContents);
        $errors = array();

        foreach ($languages as $language) {
            if (!strpos($translatedContents[$language]['content'], '%username%')) {
                $errors[$language]['content'][] = 'missing_%username%';
            }
            if (!strpos($translatedContents[$language]['content'], '%password%')) {
                $errors[$language]['content'][] = 'missing_%password%';
            }
        }

        return $errors;
    }

    /**
     * @param $translatedContents
     *
     * @return array
     */
    public function validateLayoutMail(array $translatedContents)
    {
        $languages = array_keys($translatedContents);
        $errors = array();

        foreach ($languages as $language) {
            if (!strpos($translatedContents[$language]['content'], '%content%')) {
                $errors[$language]['content'] = 'missing_%content%';
            }
        }

        return $errors;
    }

    /**
     * @DI\Observe("refresh_cache")
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        try {
            $this->mailer->getTransport()->start();
            $event->addCacheParameter('is_mailer_available', true);
        } catch (\Swift_TransportException $e) {
            $event->addCacheParameter('is_mailer_available', false);
        }
    }
}
