<?php

namespace UJM\ExoBundle\Installation;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater600200 {

    use LoggableTrait;

    private $connection;
    private $widthHeightPic = array();

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
    }

    public function preUpdate()
    {
        $this->checkInteractionGraphic();
    }

    public function postUpdate()
    {
        $this->migrateExerciseQuestionData();
        $this->migratePicture();
        $this->upTypeExercise();
        $this->dropUnusedTables();
    }

    /**
     * recover the questions of an exercise in order to inject in step_question
     */
    private function checkDocument()
    {
        $this->log('Checking document ...');

        $checkQuery = '
            SELECT *
            FROM ujm_document
        ';

        return $this->connection->query($checkQuery)->fetchAll();
    }

    /**
     * recover the documents in order to inject in picture
     */
    private function checkExoQuestion()
    {
        $this->log('Checking exercise_question ...');

        $checkQuery = '
            SELECT exercise_id, question_id, ordre
            FROM ujm_exercise_question
        ';

        return $this->connection->query($checkQuery)->fetchAll();
    }

     /**
     * recover the width and height
     * move interaction_graphic -> picture
     */
    private function checkInteractionGraphic()
    {
        $this->log('Checking InteractionGraphic ...');

        $checkQuery = '
            SELECT document_id, width, height
            FROM ujm_interaction_graphic
        ';

        $results = $this->connection->query($checkQuery)->fetchAll();
        foreach ($results as $res) {
            $wh = array($res['width'], $res['height']);
            $this->widthHeightPic[$res['document_id']] = $wh;
        }
    }

    /**
     * update type of an exercise
     */
    private function upTypeExercise ()
    {
        $this->log('UPDATE type of exercise ...');

        $this->connection->exec("
            UPDATE ujm_exercise
            SET type = 'sommatif'
        ");
    }

    /**
     *
     * Remove unused tables
     */
    private function dropUnusedTables()
    {
        $this->dropTables([
            'ujm_document',
            'ujm_exercise_question'
        ]);
    }

    /**
     * Move data exercise_question in step_question
     */
    private function migrateExerciseQuestionData()
    {
        $exoId  = -1;
        $stepId = -1;

        $exoQuestion = $this->checkExoQuestion();

        foreach ($exoQuestion as $eq) {
            if ($eq['exercise_id'] != $exoId) {
                $exoId = $eq['exercise_id'];
                $stepId = $this->newStep($exoId);
            }

            $this->addQuestionStep($stepId, $eq['question_id'], $eq['ordre']);

        }
    }

    /**
     * Move data document in picture
     */
    private function migratePicture()
    {
        $documents = $this->checkDocument();

        foreach ($documents as $doc) {
            $this->connection->exec("
                INSERT INTO ujm_picture
                ({$doc['id']}, {$doc['label']}, {$doc['url']}, {$doc['type']}, $this->widthHeightPic[$doc['id']][0] , $this->widthHeightPic[$doc['id']][1])
            ");
        }
    }

    /**
     * Create one step for each exercise
     *
     * @param Integer $exoId
     */
    private function newStep($exoId)
    {
        $this->connection->exec("
            INSERT INTO ujm_step
            ('exercise_id', 'value', 'nbQuestion', 'keepSameQuestion', 'shuffle', 'duration', 'max_attempts', 'ordre')
            VALUES
            ({$exoId}, '', 0, FALSE, FALSE, 0, 0, 1)
        ");

        $query = 'SELECT * FROM ujm_step WHERE exercise_id=' . $exoId;
        $step = $this->connection->query($query)->fetch();

        return $step;
    }

    /**
     *
     * @param Integer $stepId
     * @param Integer $qid
     * @param Integer $order
     */
    private function addQuestionStep($stepId, $qid, $order)
    {
        $this->connection->exec("
            INSERT INTO ujm_step_question
            ({$stepId}, {$qid}, {$order})
        ");
    }

    private function dropTables(array $tables)
    {
        $schema = $this->connection->getSchemaManager();
        $tableNames = $schema->listTableNames();

        foreach ($tables as $tableName) {
            if (in_array($tableName, $tableNames)) {
                $this->log("Dropping {$tableName} table...");
                $schema->dropTable($tableName);
            }
        }
    }
}
