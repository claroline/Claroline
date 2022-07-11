<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\TransferBundle\Transfer\Importer\AbstractDeleteImporter;

class Delete extends AbstractDeleteImporter
{
    public static function getAction(): array
    {
        return ['organization', self::MODE_DELETE];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }
}
