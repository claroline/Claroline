<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class RemoveUser extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['group', 'remove_user'];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }

    protected static function getCollectionClass(): string
    {
        return User::class;
    }
}
