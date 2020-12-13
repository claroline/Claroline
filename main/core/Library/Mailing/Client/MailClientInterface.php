<?php

namespace Claroline\CoreBundle\Library\Mailing\Client;

use Claroline\CoreBundle\Library\Mailing\Message;

interface MailClientInterface
{
    public function getTransports();

    public function send(Message $message);
}
