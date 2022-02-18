<?php

namespace Claroline\CoreBundle\Transfer\Importer\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Importer\AbstractDeleteImporter;

class Delete extends AbstractDeleteImporter
{
    public function getAction(): array
    {
        return ['group', self::MODE_DELETE];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }
}
