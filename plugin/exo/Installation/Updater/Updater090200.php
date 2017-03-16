<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\DBAL\Connection;

class Updater090200
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
        $this->log('Remove all html tags from ujm_word_response (keywords)...');

        $sth = $this->connection->prepare('SELECT id, response FROM ujm_word_response');
        $sth->execute();
        $keywords = $sth->fetchAll();

        foreach ($keywords as $keyword) {
            $sth = $this->connection->prepare('UPDATE ujm_word_response SET `response` = :newResponse WHERE `id` = :id');
            $sth->execute([
                ':newResponse' => strip_tags(html_entity_decode($keyword['response'])),
                ':id' => $keyword['id'],
            ]);
        }
    }
}
