<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['workspace', 'list'];
    }

    protected static function getClass(): string
    {
        return Workspace::class;
    }
}
