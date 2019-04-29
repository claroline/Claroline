<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

class Updater120403 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->convertDuration();
        $this->convertBooleanAnswers();
    }

    private function convertDuration()
    {
        $this->log('Convert quiz duration to seconds...');

        $sql = 'UPDATE ujm_exercise SET duration = (duration * 60) WHERE duration IS NOT NULL AND duration != 0';
        $sth = $this->container->get('doctrine.dbal.default_connection')->prepare($sql);
        $sth->execute();
    }

    private function convertBooleanAnswers()
    {
        $this->log('Convert boolean answers...');

        $sql = '
            UPDATE ujm_response AS r
            JOIN ujm_question AS q ON (q.uuid = r.question_id)
            SET r.response = CONCAT("[", r.response, "]")
            WHERE r.response IS NOT NULL AND r.response != ""
              AND q.id IS NOT NULL
              AND q.mime_type = "application/x.choice+json"
              AND LEFT(r.response, 1) != "["
        ';
        $sth = $this->container->get('doctrine.dbal.default_connection')->prepare($sql);
        $sth->execute();
    }
}
