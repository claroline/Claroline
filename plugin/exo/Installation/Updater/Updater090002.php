<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\DBAL\Connection;

class Updater090002
{
    use LoggableTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Updater090002 constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function postUpdate()
    {
        $this->fixQuizTypes();
    }

    private function fixQuizTypes()
    {
        $this->connection
            ->prepare('
                UPDATE ujm_exercise SET `type` = "summative" WHERE `type` = "1";
            ')
            ->execute();

        $this->connection
            ->prepare('
                UPDATE ujm_exercise SET `type` = "summative" WHERE `type` = "sommatif";
            ')
            ->execute();

        $this->connection
            ->prepare('
                UPDATE ujm_exercise SET `type` = "evaluative" WHERE `type` = "2";
            ')
            ->execute();

        $this->connection
            ->prepare('
                UPDATE ujm_exercise SET `type` = "formative" WHERE `type` = "3";
            ')
            ->execute();
    }
}
