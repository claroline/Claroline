<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCollectionImporter;

class AddManager extends AbstractCollectionImporter
{
    public static function getAction(): array
    {
        return ['organization', 'add_manager'];
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
