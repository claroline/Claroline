<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Mailing;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Mailing\Client\MailClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Mailer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $clients = [];

    public function __construct(
        private readonly PlatformConfigurationHandler $ch
    ) {
    }

    public function send(Message $message): bool
    {
        $client = $this->getClient();

        if (empty($message->getAttribute('to')) && empty($message->getAttribute('bcc'))) {
            $this->logger->error('To field is either empty or invalid');

            return false;
        }
        $to = count($message->getAttribute('to')) > 0 ? $message->getAttribute('to')[0] : $message->getAttribute('bcc')[0];

        try {
            $client->send($message);
            $this->logger->info('Email sent to '.$to);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Fail to send email to '.$to);
            $this->logger->error($e->getMessage());
            $this->logger->error(json_encode($message));

            return false;
        }
    }

    public function add(MailClientInterface $client): void
    {
        $this->clients[] = $client;
    }

    public function getClient(): MailClientInterface
    {
        $transport = $this->ch->getParameter('mailer.transport');

        foreach ($this->clients as $client) {
            if (in_array($transport, $client->getTransports())) {
                return $client;
            }
        }

        throw new \Exception('Transport '.$transport.' not found.');
    }
}
