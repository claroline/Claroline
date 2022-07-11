<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class RemoveGroup extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['organization', 'remove_group'];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }

    protected static function getCollectionClass(): string
    {
        return Group::class;
    }
}
