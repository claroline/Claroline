<?php

namespace Claroline\CoreBundle\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Mailing\Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SymfonyMailer implements MailClientInterface
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function getTransports(): array
    {
        return ['smtp', 'gmail', 'sendmail'];
    }

    public function send(Message $message): void
    {
        $email = new Email();
        $email->subject($message->getAttribute('subject'));
        $email->from($message->getAttribute('from'));
        $email->to(...$message->getAttribute('to'));
        $email->html($message->getAttribute('body'));

        if ($message->getAttribute('reply_to')) {
            $email->replyTo($message->getAttribute('reply_to'));
        }
        if ($message->getAttribute('bcc')) {
            $email->bcc(...$message->getAttribute('bcc'));
        }

        foreach ($message->getAttribute('attachments') as $attachment) {
            $email->attach(file_get_contents($attachment['url']), $attachment['name'], $attachment['type']);
        }

        $this->mailer->send($email);
    }
}
