<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\DBAL\Connection;

class Updater060000
{
    use LoggableTrait;

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function preUpdate()
    {
        $this->migrateDateData();
        $this->checkQuestionReferences();
    }

    public function postUpdate()
    {
        $this->dropExpertiseTables();
        $this->migrateInteractionData();
        $this->dropInteractionTables();
    }

    /**
     * Date control access, as well as creation dates, move
     * from the exercises to the resource nodes themselves.
     */
    private function migrateDateData()
    {
        if (!$this->connection->getSchemaManager()->listTableDetails('ujm_exercise')->hasColumn('start_date')) {
            return; // migration has already been executed
        }

        $this->log('Moving date data from ujm_exercise to claro_resource_node...');

        $startQuery = '
            UPDATE claro_resource_node AS node
            JOIN ujm_exercise AS exo
            ON node.id = exo.resourceNode_id
            SET node.accessible_from = exo.start_date
            WHERE node.accessible_from IS NULL
            AND exo.start_date IS NOT NULL
        ';
        $endQuery = '
            UPDATE claro_resource_node AS node
            JOIN ujm_exercise AS exo
            ON node.id = exo.resourceNode_id
            SET node.accessible_until = exo.end_date
            WHERE node.accessible_until IS NULL
            AND exo.start_date IS NOT NULL
            AND exo.use_date_end = 1
        ';

        $this->connection->exec($startQuery);
        $this->connection->exec($endQuery);
    }

    /**
     * One part of the migration consists in merging the
     * Interaction entity into the Question entity. For the
     * merge to be successful, we must ensure that previous
     * references to the interaction table point to the question
     * table. In most installations, this won't require any
     * effort, because despite their one-to-many relationship,
     * these two entities have always been used in a one-to-one
     * fashion. Having always been created and deleted altogether,
     * their generated id is expected to be identical. Thus, simply
     * renaming an "interaction_id" to a "question_id" and make
     * it point to the question table should be sufficient.
     * However, in the unlikely event that identifiers are
     * different, all foreign keys must be updated properly.
     */
    private function checkQuestionReferences()
    {
        if (!in_array('ujm_interaction', $this->connection->getSchemaManager()->listTableNames())) {
            return; // migration has already been executed
        }

        $this->log('Checking question references...');

        $checkQuery = '
            SELECT id AS interaction_id, question_id
            FROM ujm_interaction
            WHERE id <> question_id
        ';
        $divergentIds = $this->connection->query($checkQuery)->fetchAll();

        if (count($divergentIds) > 0) {
            $this->log('Found diverging identifiers, looking for references to update...');

            // key = table, value = name of the foreign key on "interaction_id"
            $candidateTables = [
                'ujm_hint' => 'FK_B5FFCBE7886DEE8F',
                'ujm_interaction_graphic' => 'FK_9EBD442F886DEE8F',
                'ujm_interaction_hole' => 'FK_7343FAC1886DEE8F',
                'ujm_interaction_matching' => 'FK_AC9801C7886DEE8F',
                'ujm_interaction_open' => 'FK_BFFE44F4886DEE8F',
                'ujm_interaction_qcm' => 'FK_58C3D5A1886DEE8F',
                'ujm_response' => 'FK_A7EC2BC2886DEE8F',
            ];

            // if values need to be changed in those tables, unique indexes must be dropped/restored
            $uniqueIndexes = [
                'ujm_interaction_graphic' => 'UNIQ_9EBD442F886DEE8F',
                'ujm_interaction_hole' => 'UNIQ_7343FAC1886DEE8F',
                'ujm_interaction_matching' => 'UNIQ_AC9801C7886DEE8F',
                'ujm_interaction_qcm' => 'UNIQ_58C3D5A1886DEE8F',
                'ujm_interaction_open' => 'UNIQ_BFFE44F4886DEE8F',
            ];

            // makes the result set more usable (key = interaction_id, value = question _id)
            $divergentByInteraction = [];

            foreach ($divergentIds as $divergentPair) {
                $divergentByInteraction[$divergentPair['interaction_id']] = $divergentPair['question_id'];
            }

            $idChain = implode(',', array_keys($divergentByInteraction));

            foreach ($candidateTables as $table => $foreignKey) {
                $referenceQuery = "
                    SELECT id, interaction_id
                    FROM {$table}
                    WHERE interaction_id IN ({$idChain})
                ";
                $foundIds = $this->connection->query($referenceQuery)->fetchAll();

                if (count($foundIds) > 0) {
                    $this->log("Found reference(s) in {$table}, updating...");

                    // foreign key must be dropped to update value
                    $this->connection->exec("
                        ALTER TABLE {$table}
                        DROP FOREIGN KEY {$foreignKey};
                    ");

                    if (in_array($table, array_keys($uniqueIndexes))) {
                        // unique index must be dropped too
                        $this->connection->exec("
                            DROP INDEX {$uniqueIndexes[$table]} ON {$table};
                        ");
                    }

                    foreach ($foundIds as $idRow) {
                        $this->connection->exec("
                            UPDATE {$table}
                            SET interaction_id = {$divergentByInteraction[$idRow['interaction_id']]}
                            WHERE id = {$idRow['id']}
                        ");
                    }

                    // restore foreign key (so that it can be dropped by the migration file)
                    // BUT make it already point to question (to avoid references issues)
                    $restoreQuery = "
                        ALTER TABLE {$table}
                        ADD CONSTRAINT {$foreignKey} FOREIGN KEY (interaction_id)
                        REFERENCES ujm_question (id)
                    ";

                    if (in_array($table, array_keys($uniqueIndexes))) {
                        // restore unique index too
                        $this->connection->exec("
                            CREATE INDEX {$uniqueIndexes[$table]} ON {$table} (interaction_id);
                        ");
                    }

                    $this->connection->exec($restoreQuery);
                }
            }
        }
    }

    /**
     * Remove unused tables related to "expertise" functionality.
     */
    private function dropExpertiseTables()
    {
        $schema = $this->connection->getSchemaManager();
        if ($schema->tablesExist(['ujm_expertise_user'])) {
            $this->dropTables([
                'ujm_expertise_user',
                'ujm_expertise',
                'ujm_exercise_group',
                'ujm_planning',
                'ujm_group',
            ]);
        }
    }

    /**
     * Move data from interaction table to question table (merge).
     */
    private function migrateInteractionData()
    {
        if (!in_array('ujm_interaction', $this->connection->getSchemaManager()->listTableNames())) {
            return; // migration has already been executed
        }

        $this->log('Moving data from ujm_interaction to ujm_question...');

        $typeQuery = '
            UPDATE ujm_question AS question
            JOIN ujm_interaction AS interaction
            ON question.id = interaction.question_id
            SET question.type = interaction.type
        ';
        $inviteQuery = '
            UPDATE ujm_question AS question
            JOIN ujm_interaction AS interaction
            ON question.id = interaction.question_id
            SET question.invite = interaction.invite
        ';
        $feedbackQuery = '
            UPDATE ujm_question AS question
            JOIN ujm_interaction AS interaction
            ON question.id = interaction.question_id
            SET question.feedback = interaction.feedback
        ';

        $this->connection->exec($typeQuery);
        $this->connection->exec($inviteQuery);
        $this->connection->exec($feedbackQuery);
    }

    /**
     * Remove unused tables after the interaction/question merge.
     */
    private function dropInteractionTables()
    {
        $schema = $this->connection->getSchemaManager();
        if ($schema->tablesExist(['ujm_interaction'])) {
            $this->dropTables([
                'ujm_document_interaction',
                'ujm_interaction',
            ]);
        }
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
