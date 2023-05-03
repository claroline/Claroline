<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater130800 extends Updater
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function preUpdate(): void
    {
        $this->connection->executeQuery('UPDATE claro_cursusbundle_course SET session_duration = (session_duration * 24) WHERE session_duration IS NOT NULL');
    }
}
