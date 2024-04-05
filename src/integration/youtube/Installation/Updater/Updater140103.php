<?php

namespace Claroline\YouTubeBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Updater140103 extends Updater
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function postUpdate(): void
    {
        $updateControls = $this->connection->prepare('
            UPDATE claro_youtube_video 
            AS ytv 
            SET ytv.controls = true
        ');

        $updateControls->executeQuery();
    }
}
