<?php

namespace UJM\ExoBundle\Installation;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater700100
{
    use LoggableTrait;

    private $connection;

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
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
        if ($res->rowCount() == 0) {
            $this->connection->exec("
                INSERT INTO ujm_type_matching VALUES(3,'To pair', 3)
            ");
        }
    }
}
