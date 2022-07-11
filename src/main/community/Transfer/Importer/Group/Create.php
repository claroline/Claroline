<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Importer\AbstractCreateImporter;

class Create extends AbstractCreateImporter
{
    public static function getAction(): array
    {
        return ['group', self::MODE_CREATE];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }
}
