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

use Claroline\AppBundle\Log\FileLogger;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class Mailer
{
    private $ch;
    private $clients = [];
    private $logger;

    public function __construct(
        PlatformConfigurationHandler $ch,
        string $logDir
    ) {
        $this->ch = $ch;
        $this->logger = FileLogger::get($logDir.'/email.log');
    }

    public function send(Message $message)
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

    public function add($client)
    {
        $this->clients[] = $client;
    }

    public function getClient()
    {
        $transport = $this->ch->getParameter('mailer_transport');

        foreach ($this->clients as $client) {
            if (in_array($transport, $client->getTransports())) {
                return $client;
            }
        }

        throw new \Exception('Transport '.$transport.' not found.');
    }
}
