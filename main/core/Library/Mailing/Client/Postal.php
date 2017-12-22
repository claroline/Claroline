<?php

namespace Claroline\CoreBundle\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Mailing\Message;
use JMS\DiExtraBundle\Annotation as DI;
use Postal\Client;
use Postal\SendMessage;

/**
 * @DI\Service("claroline.mailing.postal")
 * @DI\Tag("claroline.mailing")
 */
class Postal implements MailClientInterface
{
    /**
     * @DI\InjectParams({
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $ch)
    {
        $this->ch = $ch;
    }

    public function getTransports()
    {
        return['postal'];
    }

    public function test(array $data)
    {
        return [];
    }

    public function send(Message $message)
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

        if ($tag = $this->ch->getParameter('mailer_tag')) {
            $sendMessage->tag($tag);
        }

        $sendMessage->subject($message->getAttribute('subject'));
        $sendMessage->htmlBody($message->getAttribute('body'));

        if ($message->hasAttribute('reply_to')) {
            $sendMessage->replyTo($message->getAttribute('reply_to'));
        }

        return $sendMessage->send();
    }
}
