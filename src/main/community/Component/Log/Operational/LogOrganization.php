<?php

namespace Claroline\CommunityBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogOrganization extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'organization';
    }

    protected static function getEntityClass(): string
    {
        return Organization::class;
    }
}
