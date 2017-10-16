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
use Claroline\CoreBundle\Library\Logger\FileLogger;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.library.mailing.mailer")
 */
class Mailer
{
    private $mailer;
    private $ch;
    private $rootDir;

    /**
     * @DI\InjectParams({
     *     "rootDir" = @DI\Inject("%kernel.root_dir%"),
     *     "ch"      = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $ch,
        $rootDir
    ) {
        $this->ch = $ch;
        $this->rootDir = $rootDir;
        $this->clients = [];
    }

    public function send(Message $message)
    {
        $client = $this->getClient();
        $rightsLog = $this->rootDir.'/logs/email.log';
        $logger = FileLogger::get($rightsLog);

        if (empty($message->getAttribute('to')) && empty($message->getAttribute('bcc'))) {
            $logger->error('To field is either empty or invalid');

            return;
        }

        try {
            $client->send($message);
            $logger->info('Email sent to '.$message->getAttribute('to')[0]);
        } catch (\Exception $e) {
            $logger->error('Fail to send email to '.$message->getAttribute('to')[0]);
        }
    }

    public function add($client)
    {
        $this->clients[] = $client;
    }

    public function test($data)
    {
        return $this->getClient($data['transport'])->test($data);
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
