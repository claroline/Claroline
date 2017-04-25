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
     * Updater090200 constructor.
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
        $this->cleanQuestions();
    }

    /**
     * On some instance there are some old question parts that have not been correctly removed.
     */
    private function cleanQuestions()
    {
        $this->log('Delete truncated question from DB.');

        $sth = $this->connection->prepare('DELETE FROM ujm_question WHERE mime_type IS NULL OR mime_type = ""');
        $sth->execute();
    }
}
