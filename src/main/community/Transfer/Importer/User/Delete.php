<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractDeleteImporter;

class Delete extends AbstractDeleteImporter
{
    public function getAction(): array
    {
        return ['user', self::MODE_DELETE];
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    protected function getOptions(): array
    {
        return [Options::SOFT_DELETE];
    }
}
