<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class RemoveUser extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['organization', 'remove_user'];
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
