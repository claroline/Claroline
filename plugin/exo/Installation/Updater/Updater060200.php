<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\DBAL\Connection;

class Updater060200
{
    use LoggableTrait;

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function preUpdate()
    {
        $this->createTemporaryInterGraph();
        $this->createTemporaryPicture();
    }

    public function postUpdate()
    {
        $this->migrateExerciseQuestionData();
        $this->migratePicture();
        $this->upExercise();
        $this->insertScorePaper();
        $this->dropUnusedTables();
    }

    /**
     * create temporary table for picture in order to recover data.
     */
    private function createTemporaryPicture()
    {
        $schema = $this->connection->getSchemaManager();
        if (!$schema->tablesExist('ujm_picture_temp')) {
            $this->log('Create ujm_picture_temp ...');
            $this->connection->exec('
                CREATE TABLE ujm_picture_temp (
                    id INT NOT NULL,
                    user_id INT DEFAULT NULL,
                    `label` VARCHAR(255) NOT NULL,
                    url VARCHAR(255) NOT NULL,
                    type VARCHAR(255) NOT NULL,
                    width INT NOT NULL,
                    height INT NOT NULL,
                    INDEX IDX_88AACC8AA76ED395 (user_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
            ');
        }

        $this->checkDocument();
    }

    /**
     * create temporary table for interaction_graphic in order to recover picture_id.
     */
    private function createTemporaryInterGraph()
    {
        $schema = $this->connection->getSchemaManager();
        if (!$schema->tablesExist('ujm_interaction_graphic_temp')) {
            $this->log('Create ujm_interaction_graphic_temp ...');
            $this->connection->exec('
                CREATE TABLE ujm_interaction_graphic_temp (
                    id INT NOT NULL,
                    document_id INT NOT NULL,
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
            ');
        }

        $this->connection->exec('
            INSERT INTO ujm_interaction_graphic_temp (id, document_id)
            SELECT id, document_id
            FROM ujm_interaction_graphic
        ');
    }

    /**
     * recover the questions of an exercise in order to inject in step_question.
     */
    private function checkDocument()
    {
        $this->log('Checking document ...');

        $checkQuery = '
            SELECT *
            FROM ujm_document
        ';

        $results = $this->connection->query($checkQuery)->fetchAll();
        foreach ($results as $res) {
            try {
                $this->connection->exec("
                INSERT INTO ujm_picture_temp
                VALUES ({$res['id']}, {$res['user_id']}, '"
                .addslashes($res['label'])."', '"
                .addslashes($res['url'])."', '"
                .addslashes($res['type'])."',
                0, 0
                )
            ");
            } catch (\Exception $e) {
                $this->log("Picture {$res['id']} already inserted");
            }
        }

        $this->checkInteractionGraphic();
    }

    /**
     * recover the documents in order to inject in picture.
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
     * move interaction_graphic -> picture.
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
            $this->connection->exec("
                UPDATE ujm_picture_temp
                SET width = {$res['width']}, height = {$res['height']}
                WHERE id = {$res['id']}
            ");
        }

        $this->connection->exec('
            UPDATE ujm_interaction_graphic
            SET document_id = NULL
        ');
    }

    /**
     * update exercise, type and anonymous.
     */
    private function upExercise()
    {
        $this->log('UPDATE type of exercise ...');

        $this->connection->exec("
            UPDATE ujm_exercise
            SET type = 'sommatif', anonymous = FALSE
        ");
    }

    /**
     * Remove unused tables.
     */
    private function dropUnusedTables()
    {
        $schema = $this->connection->getSchemaManager();
        if ($schema->tablesExist(['ujm_document_question'])) {
            $this->dropTables([
                'ujm_document_interaction',
                'ujm_document_question',
                'ujm_document',
                'ujm_exercise_question',
                'ujm_picture_temp',
                'ujm_interaction_graphic_temp',
            ]);
        }
    }

    /**
     * Move data exercise_question in step_question.
     */
    private function migrateExerciseQuestionData()
    {
        $exoId = -1;
        $orderStep = 1;

        $exoQuestion = $this->checkExoQuestion();
        $stepStmt = $this->connection->prepare("
            INSERT INTO ujm_step
            (exercise_id, value, nbQuestion, keepSameQuestion, shuffle, duration, max_attempts, ordre)
            VALUES (:exoId, '', 0, FALSE, FALSE, 0, 0, :orderStep)
        ");

        foreach ($exoQuestion as $eq) {
            if ($eq['exercise_id'] !== $exoId) {
                $exoId = $eq['exercise_id'];
            }

            $this->log('Create step ...');

            $stepStmt->bindValue('exoId', $exoId);
            $stepStmt->bindValue('orderStep', $orderStep);
            $stepStmt->execute();

            $stepId = $this->connection->lastInsertId();
            $this->addQuestionStep($stepId, $eq['question_id'], 1);
            ++$orderStep;
        }
    }

    /**
     * Move data picture_temp in picture.
     */
    private function migratePicture()
    {
        $this->log('UPDATE Picture ...');

        $this->connection->exec('
            INSERT INTO ujm_picture (id, user_id, `label`, url, type, width, height)
            SELECT id, user_id, `label`, url, type, width, height
            FROM ujm_picture_temp
        ');

        $this->log('UPDATE interaction_graphic -> picture_id ...');
        $query = 'SELECT * FROM ujm_interaction_graphic_temp';
        $results = $this->connection->query($query)->fetchAll();
        $stmt = $this->connection->prepare('
            UPDATE ujm_interaction_graphic
            SET picture_id = :docId
            WHERE id = :resId
        ');

        foreach ($results as $res) {
            $stmt->bindValue('docId', $res['document_id']);
            $stmt->bindValue('resId', $res['id']);
            $stmt->execute();
        }
    }

    /**
     * @param int $stepId
     * @param int $qid
     * @param int $order
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
        $query = 'SELECT * FROM ujm_paper WHERE end IS NOT NULL';
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
                .'FROM ujm_response '
                .'WHERE paper_id='.$idPaper;
        $result = $this->connection->query($query)->fetch();

        if (empty($result['score'])) {
            $score = 0;
        } else {
            $score = $result['score'];
        }
        $this->updatePaper($score, $idPaper);
    }

    /*
     * @param Integer $score
     * @param Interger $idPaper
     * insert the score in Paper
     */
    private function updatePaper($score, $idPaper)
    {
        $this->log('UPDATE Paper ...');

        $stmt = $this->connection->prepare('
            UPDATE ujm_paper
            SET score = :score
            WHERE id = :idPaper
        ');
        $stmt->bindValue('score', $score);
        $stmt->bindValue('idPaper', $idPaper);
        $stmt->execute();
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
