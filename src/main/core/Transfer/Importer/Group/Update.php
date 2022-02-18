<?php

namespace Claroline\CoreBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Importer\AbstractUpdateImporter;

class Update extends AbstractUpdateImporter
{
    public function getAction(): array
    {
        return ['group', self::MODE_UPDATE];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }
}
