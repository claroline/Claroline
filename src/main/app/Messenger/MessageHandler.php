<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Messenger;

use Claroline\CoreBundle\Library\Mailing\Client\SymfonyMailer;
use Claroline\CoreBundle\Library\Mailing\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MessageHandler implements MessageHandlerInterface
{
    private $symfonyMailer;
    private $logger;

    public function __construct(SymfonyMailer $symfonyMailer, LoggerInterface $logger)
    {
        $this->symfonyMailer = $symfonyMailer;
        $this->logger = $logger;
    }

    public function __invoke(Message $message)
    {
        try {
            $this->symfonyMailer->send($message);

            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error(json_encode($message));

            return false;
        }
    }
}
