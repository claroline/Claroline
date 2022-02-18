<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListUsersExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['workspace', 'list_users'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        if (in_array(Options::WORKSPACE_IMPORT, $options) && in_array($format, ['json', 'csv'])) {
            // only in workspace tool for now because we cannot configure the workspace
            return true;
        }

        return false;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
