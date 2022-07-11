<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\TransferBundle\Transfer\Importer\AbstractCreateImporter;

class Create extends AbstractCreateImporter
{
    public static function getAction(): array
    {
        return ['organization', self::MODE_CREATE];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }
}
