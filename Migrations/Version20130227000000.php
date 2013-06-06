<?php

namespace UJM\ExoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Claroline\CoreBundle\Library\Installation\BundleMigration;

class Version20130227000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createExerciseTable($schema);
        $this->createSubscriptionTable($schema);
        $this->createGroupTable($schema);
        $this->createPlanningTable($schema);
        $this->createExerciseGroupTable($schema);
        $this->createCategoryTable($schema);
        $this->createExpertiseTable($schema);
        $this->createExpertiseUserTable($schema);
        $this->createQuestionTable($schema);
        $this->createExerciseQuestionTable($schema);
        $this->createInteractionTable($schema);
        $this->createTypeOpenQuestionTable($schema);
        $this->createInteractionOpenTable($schema);
        $this->createUnitTable($schema);
        $this->createInteractionOpenUnitTable($schema);
        $this->createHintTable($schema);
        $this->createPaperTable($schema);
        $this->createResponseTable($schema);
        $this->createLockAttemptTable($schema);
        $this->createLinkHintPaperTable($schema);
        $this->createInteractionHoleTable($schema);
        $this->createHoleTable($schema);
        $this->createWordResponseTable($schema);
        $this->createDocumentTable($schema);
        $this->createDocumentQuestionTable($schema);
        $this->createDocumentInteractionTable($schema);
        $this->createInteractionGraphicTable($schema);
        $this->createTypeQcmTable($schema);
        $this->createInteractionQcmTable($schema);
        $this->createCoordsTable($schema);
        $this->createChoiceTable($schema);
        $this->createShareTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('ujm_choice');
        $schema->dropTable('ujm_coords');
        $schema->dropTable('ujm_interaction_qcm');
        $schema->dropTable('ujm_type_qcm');
        $schema->dropTable('ujm_interaction_graphic');
        $schema->dropTable('ujm_document_interaction');
        $schema->dropTable('ujm_document_question');
        $schema->dropTable('ujm_document');
        $schema->dropTable('ujm_word_response');
        $schema->dropTable('ujm_hole');
        $schema->dropTable('ujm_interaction_hole');
        $schema->dropTable('ujm_link_hint_paper');
        $schema->dropTable('ujm_lock_attempt');
        $schema->dropTable('ujm_response');
        $schema->dropTable('ujm_paper');
        $schema->dropTable('ujm_hint');
        $schema->dropTable('ujm_interaction_open_unit');
        $schema->dropTable('ujm_unit');
        $schema->dropTable('ujm_interaction_open');
        $schema->dropTable('ujm_type_open_question');
        $schema->dropTable('ujm_interaction');
        $schema->dropTable('ujm_exercise_question');
        $schema->dropTable('ujm_question');
        $schema->dropTable('ujm_expertise_user');
        $schema->dropTable('ujm_expertise');
        $schema->dropTable('ujm_category');
        $schema->dropTable('ujm_exercise_group');
        $schema->dropTable('ujm_planning');
        $schema->dropTable('ujm_group');
        $schema->dropTable('ujm_subscription');
        $schema->dropTable('ujm_exercise');
        $schema->dropTable('ujm_share');
    }

    private function createExerciseTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_exercise');
        $this->addId($table);
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('description', 'text', array('notnull' => false));
        $table->addColumn('shuffle', 'boolean', array('notnull' => false));
        $table->addColumn('nb_question', 'integer');
        $table->addColumn('date_create', 'datetime');
        $table->addColumn('duration', 'integer');
        $table->addColumn('nb_question_page', 'integer');
        $table->addColumn('doprint', 'boolean', array('notnull' => false));
        $table->addColumn('max_attempts', 'integer');
        $table->addColumn('correction_mode', 'string', array('length' => 255));
        $table->addColumn('date_correction', 'datetime', array('notnull' => false));
        $table->addColumn('mark_mode', 'string', array('length' => 255));
        $table->addColumn('start_date', 'datetime');
        $table->addColumn('use_date_end', 'boolean', array('notnull' => false));
        $table->addColumn('end_date', 'datetime', array('notnull' => false));
        $table->addColumn('disp_button_interrupt', 'boolean', array('notnull' => false));
        $table->addColumn('lock_attempt', 'boolean', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'),
            array('id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createSubscriptionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_subscription');
        $this->addId($table);
        $table->addColumn('creator', 'boolean');
        $table->addColumn('admin', 'boolean');
        $table->addColumn('user_id', 'integer');
        $table->addColumn('exercise_id', 'integer');
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_exercise'),
            array('exercise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addUniqueIndex(array('user_id', 'exercise_id'));
    }

    private function createGroupTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_group');
        $this->addId($table);
        $table->addColumn('name', 'string', array('length' => 255));
        $this->storeTable($table);
    }

    private function createPlanningTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_planning');
        $this->addId($table);
        $table->addColumn('start_time', 'datetime');
        $table->addColumn('end_time', 'datetime');
        $table->addColumn('group_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_group'),
            array('group_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createExerciseGroupTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_exercise_group');
        $table->addColumn('exercise_id', 'integer');
        $table->addColumn('group_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_exercise'),
            array('exercise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_group'),
            array('group_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('exercise_id', 'group_id'));
    }

    private function createExpertiseTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_expertise');
        $this->addId($table);
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('description', 'text', array('notnull' => false));
        $table->addColumn('status', 'string', array('length' => 255));
        $this->storeTable($table);
    }

    private function createExpertiseUserTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_expertise_user');
        $table->addColumn('expertise_id', 'integer');
        $table->addColumn('user_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_expertise'),
            array('expertise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('expertise_id', 'user_id'));
    }

    private function createQuestionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_question');
        $this->addId($table);
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('description', 'text', array('notnull' => false));
        $table->addColumn('date_create', 'datetime');
        $table->addColumn('date_modify', 'datetime', array('notnull' => false));
        $table->addColumn('locked', 'boolean', array('notnull' => false));
        $table->addColumn('model', 'boolean', array('notnull' => false));
        $table->addColumn('expertise_id', 'integer', array('notnull' => false));
        $table->addColumn('user_id', 'integer', array('notnull' => false));
        $table->addColumn('category_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_expertise'),
            array('expertise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_category'),
            array('category_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createExerciseQuestionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_exercise_question');
        $table->addColumn('ordre', 'integer');
        $table->addColumn('exercise_id', 'integer');
        $table->addColumn('question_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_exercise'),
            array('exercise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_question'),
            array('question_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('exercise_id', 'question_id'));
    }

    private function createInteractionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction');
        $this->addId($table);
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('invite', 'text');
        $table->addColumn('ordre', 'integer', array('notnull' => false));
        $table->addColumn('feedback', 'text', array('notnull' => false));
        $table->addColumn('locked_expertise', 'boolean', array('notnull' => false));
        $table->addColumn('question_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_question'),
            array('question_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createTypeOpenQuestionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_type_open_question');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $this->storeTable($table);
    }

    private function createInteractionOpenTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction_open');
        $this->addId($table);
        $table->addColumn('orthography_correct', 'boolean');
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addColumn('typeopenquestion_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_type_open_question'),
            array('typeopenquestion_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createUnitTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_unit');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $this->storeTable($table);
    }

    private function createInteractionOpenUnitTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction_open_unit');
        $table->addColumn('interaction_open_id', 'integer');
        $table->addColumn('unit_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction_open'),
            array('interaction_open_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_unit'),
            array('unit_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('interaction_open_id', 'unit_id'));
    }

    private function createHintTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_hint');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $table->addColumn('penalty', 'float');
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createPaperTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_paper');
        $this->addId($table);
        $table->addColumn('num_paper', 'integer');
        $table->addColumn('start', 'datetime');
        $table->addColumn('end', 'datetime', array('notnull' => false));
        $table->addColumn('ordre_question', 'text', array('notnull' => false));
        $table->addColumn('archive', 'boolean', array('notnull' => false));
        $table->addColumn('date_archive', 'date', array('notnull' => false));
        $table->addColumn('interupt', 'boolean', array('notnull' => false));
        $table->addColumn('user_id', 'integer', array('notnull' => false));
        $table->addColumn('exercise_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_exercise'),
            array('exercise_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createResponseTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_response');
        $this->addId($table);
        $table->addColumn('ip', 'string', array('length' => 255));
        $table->addColumn('mark', 'float');
        $table->addColumn('nb_tries', 'integer');
        $table->addColumn('response', 'text', array('notnull' => false));
        $table->addColumn('paper_id', 'integer', array('notnull' => false));
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_paper'),
            array('paper_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createLockAttemptTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_lock_attempt');
        $this->addId($table);
        $table->addColumn('key_lock', 'string', array('length' => 255));
        $table->addColumn('date', 'date');
        $table->addColumn('paper_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_paper'),
            array('paper_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createLinkHintPaperTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_link_hint_paper');
        $table->addColumn('view', 'boolean');
        $table->addColumn('hint_id', 'integer');
        $table->addColumn('paper_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_hint'),
            array('hint_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_paper'),
            array('paper_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('hint_id', 'paper_id'));
    }

    private function createInteractionHoleTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction_hole');
        $this->addId($table);
        $table->addColumn('html', 'text');
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createHoleTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_hole');
        $this->addId($table);
        $table->addColumn('size', 'integer');
        $table->addColumn('score', 'float');
        $table->addColumn('position', 'integer');
        $table->addColumn('orthography', 'boolean');
        $table->addColumn('interaction_hole_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction_hole'),
            array('interaction_hole_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createWordResponseTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_word_response');
        $this->addId($table);
        $table->addColumn('response', 'string', array('length' => 255));
        $table->addColumn('score', 'float');
        $table->addColumn('interactionopen_id', 'integer', array('notnull' => false));
        $table->addColumn('hole_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction_open'),
            array('interactionopen_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_hole'),
            array('hole_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createDocumentTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_document');
        $this->addId($table);
        $table->addColumn('label', 'string', array('length' => 255));
        $table->addColumn('url', 'string', array('length' => 255));
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('user_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createDocumentQuestionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_document_question');
        $table->addColumn('document_id', 'integer');
        $table->addColumn('question_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_document'),
            array('document_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_question'),
            array('question_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('document_id', 'question_id'));
    }

    private function createDocumentInteractionTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_document_interaction');
        $table->addColumn('document_id', 'integer');
        $table->addColumn('interaction_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_document'),
            array('document_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('document_id', 'interaction_id'));
    }

    private function createCategoryTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_category');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $table->addColumn('user_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createTypeQcmTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_type_qcm');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $this->storeTable($table);
    }

    private function createInteractionQcmTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction_qcm');
        $this->addId($table);
        $table->addColumn('shuffle', 'boolean', array('notnull' => false));
        $table->addColumn('score_right_response', 'float', array('notnull' => false));
        $table->addColumn('score_false_response', 'float', array('notnull' => false));
        $table->addColumn('weight_response', 'boolean', array('notnull' => false));
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addColumn('type_qcm_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_type_qcm'),
            array('type_qcm_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createInteractionGraphicTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_interaction_graphic');
        $this->addId($table);
        $table->addColumn('width', 'integer');
        $table->addColumn('height', 'integer');
        $table->addColumn('interaction_id', 'integer', array('notnull' => false));
        $table->addColumn('document_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction'),
            array('interaction_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_document'),
            array('document_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $this->storeTable($table);
    }

    private function createChoiceTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_choice');
        $this->addId($table);
        $table->addColumn('label', 'text');
        $table->addColumn('ordre', 'integer');
        $table->addColumn('weight', 'float', array('notnull' => false));
        $table->addColumn('feedback', 'text', array('notnull' => false));
        $table->addColumn('right_response', 'boolean', array('notnull' => false));
        $table->addColumn('position_force', 'boolean', array('notnull' => false));
        $table->addColumn('interaction_qcm_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction_qcm'),
            array('interaction_qcm_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createCoordsTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_coords');
        $this->addId($table);
        $table->addColumn('value', 'string', array('length' => 255));
        $table->addColumn('shape', 'string', array('length' => 255));
        $table->addColumn('color', 'string', array('length' => 255));
        $table->addColumn('score_coords', 'float');
        $table->addColumn('size', 'float');
        $table->addColumn('interaction_graphic_id', 'integer', array('notnull' => false));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_interaction_graphic'),
            array('interaction_graphic_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createShareTable(Schema $schema)
    {
        $table = $schema->createTable('ujm_share');
        $table->addColumn('allowToModify', 'boolean');
        $table->addColumn('user_id', 'integer');
        $table->addColumn('question_id', 'integer');
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('ujm_question'),
            array('question_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->setPrimaryKey(array('user_id', 'question_id'));
    }
}