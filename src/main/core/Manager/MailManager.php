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

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Mailing\Mailer;
use Claroline\CoreBundle\Library\Mailing\Message;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Twig\Environment;

class MailManager
{
    public function __construct(
        private Environment $templating,
        private Mailer $mailer,
        private PlatformConfigurationHandler $config,
        private PlatformManager $platformManager,
        private TemplateManager $templateManager
    ) {
    }

    public function isMailerAvailable(): bool
    {
        return $this->config->getParameter('mailer.enabled') ?? false;
    }

    public function send($subject, $body, array $users, $from = null, array $extra = [], bool $force = false): bool
    {
        if (!$this->isMailerAvailable()) {
            return false;
        }

        if (0 === count($users) && (!isset($extra['to']) || 0 === count($extra['to']))) {
            // obviously, if we're not going to send anything to anyone, it's better to stop
            return false;
        }

        $fromEmail = $this->getMailerFrom();
        $replyToEmail = $this->getMailerReplyTo();
        $locale = null;
        if (1 === count($users)) {
            $locale = $users[0]->getLocale();
        }

        $body = $this->templateManager->getTemplate('email_layout', ['content' => $body], $locale);

        if ($from) {
            $body = str_replace('%sender_first_name%', $from->getFirstName(), $body);
            $body = str_replace('%sender_last_name%', $from->getLastName(), $body);

            if (filter_var($from->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $replyToEmail = $from->getEmail();
            }
        } else {
            $body = str_replace('%sender_first_name%', $this->config->getParameter('display.name'), $body);
            $body = str_replace('%sender_last_name%', '', $body);
        }

        $to = [];
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
        $message->replyTo($replyToEmail);
        $message->body($this->templating->render('@ClarolineApp/external.html.twig', [
            'baseUrl' => $this->platformManager->getUrl(),
            'title' => $subject,
            'content' => $body,
        ]));

        if (count($to) > 1) {
            $message->bcc($to);
        } else {
            $message->to($to);
        }

        if (isset($extra['attachments'])) {
            foreach ($extra['attachments'] as $attachment) {
                $message->attach($attachment['name'], $attachment['url'], $attachment['type']);
            }
        }

        return $this->mailer->send($message);
    }

    private function getMailerFrom(): ?string
    {
        if ($this->config->getParameter('internet.domain_name') && '' !== trim($this->config->getParameter('internet.domain_name'))) {
            $from = 'noreply@'.$this->config->getParameter('internet.domain_name');
            if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
                return $from;
            }
        }

        $from = $this->config->getParameter('mailer.from');
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return $from;
        }

        return null;
    }

    private function getMailerReplyTo(): ?string
    {
        $contactEmail = $this->config->getParameter('help.support_email');
        if ($contactEmail && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            return $contactEmail;
        }

        return $this->getMailerFrom();
    }
}
