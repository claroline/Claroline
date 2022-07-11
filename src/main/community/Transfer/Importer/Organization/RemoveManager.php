<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class RemoveManager extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['organization', 'remove_manager'];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }

    protected static function getCollectionClass(): string
    {
        return User::class;
    }
}
