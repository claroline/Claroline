<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractCreateOrUpdateImporter;

final class CreateOrUpdate extends AbstractCreateOrUpdateImporter
{
    public static function getAction(): array
    {
        return ['user', 'create_or_update'];
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
