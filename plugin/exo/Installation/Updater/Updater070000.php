<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\DBAL\Connection;

class Updater070000
{
    use LoggableTrait;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function preUpdate()
    {
        $this->addMatchType();
    }

    public function addMatchType()
    {
        $this->log('Add type To pair in match question types...');

        $query = '
            SELECT *
            FROM ujm_type_matching
            WHERE value = \'To pair\'
        ';
        $res = $this->connection->query($query);
        if ($res->rowCount() === 0) {
            $this->connection->exec("
                INSERT INTO ujm_type_matching VALUES(3,'To pair', 3)
            ");
        }
    }
}
