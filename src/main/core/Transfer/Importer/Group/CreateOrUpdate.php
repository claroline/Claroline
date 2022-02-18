<?php

namespace Claroline\CoreBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Importer\AbstractCreateOrUpdateImporter;

class CreateOrUpdate extends AbstractCreateOrUpdateImporter
{
    public function getAction(): array
    {
        return ['group', 'create_or_update'];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }
}
