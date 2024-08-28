<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class AddUser extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['group', 'add_user'];
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
