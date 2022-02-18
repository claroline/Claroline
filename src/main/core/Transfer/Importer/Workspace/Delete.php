<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractDeleteImporter;

class Delete extends AbstractDeleteImporter
{
    protected static function getClass(): string
    {
        return Workspace::class;
    }

    public function getAction(): array
    {
        return ['workspace', self::MODE_DELETE];
    }
}
