<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\TransferBundle\Transfer\Importer\AbstractUpdateImporter;

class Update extends AbstractUpdateImporter
{
    public static function getAction(): array
    {
        return ['organization', self::MODE_UPDATE];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }
}
