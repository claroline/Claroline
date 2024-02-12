<?php

namespace Claroline\ForumBundle\Component\Log\Operational;

use Claroline\ForumBundle\Entity\Message;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogMessage extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'forum_message';
    }

    protected static function getEntityClass(): string
    {
        return Message::class;
    }
}
