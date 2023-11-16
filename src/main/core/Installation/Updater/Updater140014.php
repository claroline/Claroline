<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater140014 extends Updater
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function postUpdate(): void
    {
        $deleteTemplateTypes = $this->connection->prepare(
            "DELETE FROM claro_template_type WHERE entity_name = 'workspace_registration' OR entity_name = 'platform_role_registration'"
        );
        $deleteTemplateTypes->executeQuery();
    }
}
