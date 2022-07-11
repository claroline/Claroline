<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Organization;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\TransferBundle\Transfer\Importer\AbstractCreateOrUpdateImporter;

class CreateOrUpdate extends AbstractCreateOrUpdateImporter
{
    public static function getAction(): array
    {
        return ['organization', 'create_or_update'];
    }

    protected static function getClass(): string
    {
        return Organization::class;
    }
}
