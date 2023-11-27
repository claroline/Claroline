<?php

namespace Claroline\CoreBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogResource extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'resource';
    }

    protected static function getEntityClass(): string
    {
        return ResourceNode::class;
    }
}
