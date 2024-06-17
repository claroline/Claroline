<?php

namespace Claroline\CoreBundle\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Mailing\Message;
use Postal\Client;
use Postal\SendMessage;

class PostalMailer implements MailClientInterface
{
    public function __construct(
        private readonly PlatformConfigurationHandler $ch
    ) {
    }

    public function getTransports(): array
    {
        return ['postal'];
    }

    public function send(Message $message): void
    {
        $client = new Client(
            $this->ch->getParameter('mailer_host'),
            $this->ch->getParameter('mailer_api_key')
        );

        // Create a new message
        $sendMessage = new SendMessage($client);
        $sendMessage->bcc($message->getAttribute('bcc'));
        $sendMessage->to($message->getAttribute('to'));
        $sendMessage->from($message->getAttribute('from'));

        $tag = $this->ch->getParameter('mailer_tag');
        if ($tag) {
            $sendMessage->tag($tag);
        }

        $sendMessage->subject($message->getAttribute('subject'));
        $sendMessage->htmlBody($message->getAttribute('body'));

        if ($message->hasAttribute('reply_to')) {
            $sendMessage->replyTo($message->getAttribute('reply_to'));
        }

        foreach ($message->getAttribute('attachments') as $attachment) {
            $sendMessage->attach($attachment['name'], $attachment['type'], file_get_contents($attachment['url']));
        }

        $sendMessage->send();
    }
}
