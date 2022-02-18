<?php

namespace Claroline\CoreBundle\Transfer\Exporter\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['user', 'list'];
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
