<?php

namespace UJM\ExoBundle\Installation;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater600200 {

    use LoggableTrait;

    private $connection;
    private $widthHeightPic = array();
    private $refPictureInInterGraph = array();

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
        $this->insertScorePaper();
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
            SELECT id, document_id, width, height
            FROM ujm_interaction_graphic
        ';

        $results = $this->connection->query($checkQuery)->fetchAll();
        foreach ($results as $res) {
            $wh = array($res['width'], $res['height']);
            $this->widthHeightPic[$res['document_id']] = $wh;
            $this->refPictureInInterGraph[$res['id']] = $res['document_id'];
        }

        /*
         * To execute :
         * ALTER TABLE ujm_interaction_graphic
            ADD CONSTRAINT FK_9EBD442FEE45BDBF FOREIGN KEY (picture_id)
            REFERENCES ujm_picture (id)
         */
        $this->connection->exec("
            UPDATE ujm_interaction_graphic
            SET document_id = NULL
        ");

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
        $this->log('UPDATE Picture ...');

        $documents = $this->checkDocument();

        $this->log('INSERT INTO ujm_picture ...');
        foreach ($documents as $doc) {
            $this->connection->exec("
                INSERT INTO ujm_picture VALUES
                ({$doc['id']}, '". addslashes($doc['label']) . "', '" 
                . addslashes($doc['url']) . "', '" 
                . addslashes($doc['type']) . "', "
                . $this->widthHeightPic[$doc['id']][0]
                . ", "
                . $this->widthHeightPic[$doc['id']][1])
                . ")";
        }

        $this->log('UPDATE Interaction_Graphic ...');
        foreach ($this->refPictureInInterGraph as $key => $ref) {
            $this->connection->exec("
                UPDATE ujm_interaction_graphic
                SET picture_id = " . $ref
                ." WHERE id = " . $key);
        }
    }

    /**
     * Create one step for each exercise
     *
     * @param Integer $exoId
     */
    private function newStep($exoId)
    {
        $this->log('UPDATE Step ...');

        $this->connection->exec("
            INSERT INTO ujm_step
            (exercise_id, value, nbQuestion, keepSameQuestion, shuffle, duration, max_attempts, ordre)
            VALUES
            ({$exoId}, '', 0, FALSE, FALSE, 0, 0, 1)
        ");

        $query = 'SELECT * FROM ujm_step WHERE exercise_id=' . $exoId;
        $step = $this->connection->query($query)->fetch();

        return $step['id'];
    }

    /**
     *
     * @param Integer $stepId
     * @param Integer $qid
     * @param Integer $order
     */
    private function addQuestionStep($stepId, $qid, $order)
    {
        $this->log('UPDATE StepQuestion ...');

        $this->connection->exec("
            INSERT INTO ujm_step_question VALUES
            ({$stepId}, {$qid}, {$order})
        ");
    }

    /*
     * add the score value in the paper entity
     */
    private function insertScorePaper()
    {
        $query = 'SELECT * FROM ujm_paper';
        $papers = $this->connection->query($query)->fetchAll();
        foreach ($papers as $paper) {
            $this->calculateScore($paper['id']);
        }
    }

    /*
     * @param Integer $idPaper
     * Calculate the score of a paper
     */
    private function calculateScore($idPaper)
    {
        $query = 'SELECT sum(mark) as score '
                . 'FROM ujm_response '
                . 'WHERE paper_id=' . $idPaper;
        $result = $this->connection->query($query)->fetch();

        $this->updatePaper($result['score']);
    }

    /*
     * @param Integer $score
     * @param Interger $idPaper
     * insert the score in Paper
     */
    private function updatePaper($score, $idPaper)
    {
        $this->log('UPDATE Paper ...');
        $query ='UPDATE ujm_paper SET score=' . $score
                . ' WHERE id=' . $idPaper;
        $this->connection->exec($query);
    }


    private function dropTables(array $tables)
    {
        $this->log('Drop tables ...');
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
