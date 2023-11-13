<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater140010 extends Updater
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function postUpdate(): void
    {
        $updateUsers = $this->connection->prepare("
            UPDATE claro_user AS u SET u.mail = CONCAT('email', CONCAT(u.id, '@deleted.com')) WHERE u.is_removed = true
        ");

        $updateUsers->executeQuery();
    }
}
