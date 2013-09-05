<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/05 03:52:14
 */
class Version20130905155213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_exercise_question (
                exercise_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                ordre INTEGER NOT NULL, 
                PRIMARY KEY(exercise_id, question_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DB79F240E934951A ON ujm_exercise_question (exercise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DB79F2401E27F6BF ON ujm_exercise_question (question_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_link_hint_paper (
                hint_id INTEGER NOT NULL, 
                paper_id INTEGER NOT NULL, 
                \"view\" BOOLEAN NOT NULL, 
                PRIMARY KEY(hint_id, paper_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B76F00F9519161AB ON ujm_link_hint_paper (hint_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B76F00F9E6758861 ON ujm_link_hint_paper (paper_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_choice (
                id INTEGER NOT NULL, 
                interaction_qcm_id INTEGER DEFAULT NULL, 
                label CLOB NOT NULL, 
                ordre INTEGER NOT NULL, 
                weight DOUBLE PRECISION DEFAULT NULL, 
                feedback CLOB DEFAULT NULL, 
                right_response BOOLEAN DEFAULT NULL, 
                position_force BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4BDFA959DBF539 ON ujm_choice (interaction_qcm_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_subscription (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                exercise_id INTEGER DEFAULT NULL, 
                creator BOOLEAN NOT NULL, 
                admin BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A17BA225A76ED395 ON ujm_subscription (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A17BA225E934951A ON ujm_subscription (exercise_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_planning (
                id INTEGER NOT NULL, 
                group_id INTEGER DEFAULT NULL, 
                start_time DATETIME NOT NULL, 
                end_time DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4D0E9FCFFE54D947 ON ujm_planning (group_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_category (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9FDB39F8A76ED395 ON ujm_category (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_type_qcm (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_hole (
                id INTEGER NOT NULL, 
                interaction_hole_id INTEGER DEFAULT NULL, 
                size INTEGER NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                position INTEGER NOT NULL, 
                orthography BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E9F4F52575EBD64D ON ujm_hole (interaction_hole_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                label VARCHAR(255) NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_41FEAA4FA76ED395 ON ujm_document (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_hole (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                html CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7343FAC1886DEE8F ON ujm_interaction_hole (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_open (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                typeopenquestion_id INTEGER DEFAULT NULL, 
                orthography_correct BOOLEAN NOT NULL, 
                scoreMaxLongResp DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_BFFE44F4886DEE8F ON ujm_interaction_open (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BFFE44F46AFD3CF ON ujm_interaction_open (typeopenquestion_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_word_response (
                id INTEGER NOT NULL, 
                interaction_open_id INTEGER DEFAULT NULL, 
                hole_id INTEGER DEFAULT NULL, 
                response VARCHAR(255) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C598DDBDFD ON ujm_word_response (interaction_open_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C515ADE12C ON ujm_word_response (hole_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_response (
                id INTEGER NOT NULL, 
                paper_id INTEGER DEFAULT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                ip VARCHAR(255) NOT NULL, 
                mark DOUBLE PRECISION NOT NULL, 
                nb_tries INTEGER NOT NULL, 
                response CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A7EC2BC2E6758861 ON ujm_response (paper_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A7EC2BC2886DEE8F ON ujm_response (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_unit (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_group (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_exercise (
                id INTEGER NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                shuffle BOOLEAN DEFAULT NULL, 
                nb_question INTEGER NOT NULL, 
                date_create DATETIME NOT NULL, 
                duration INTEGER NOT NULL, 
                nb_question_page INTEGER NOT NULL, 
                doprint BOOLEAN DEFAULT NULL, 
                max_attempts INTEGER NOT NULL, 
                correction_mode VARCHAR(255) NOT NULL, 
                date_correction DATETIME DEFAULT NULL, 
                mark_mode VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                use_date_end BOOLEAN DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                disp_button_interrupt BOOLEAN DEFAULT NULL, 
                lock_attempt BOOLEAN DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_374DF525B87FAB32 ON ujm_exercise (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_exercise_group (
                exercise_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                PRIMARY KEY(exercise_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_78179004E934951A ON ujm_exercise_group (exercise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_78179004FE54D947 ON ujm_exercise_group (group_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_lock_attempt (
                id INTEGER NOT NULL, 
                paper_id INTEGER DEFAULT NULL, 
                key_lock VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7A7CDF96E6758861 ON ujm_lock_attempt (paper_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_graphic (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                document_id INTEGER DEFAULT NULL, 
                width INTEGER NOT NULL, 
                height INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_9EBD442F886DEE8F ON ujm_interaction_graphic (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9EBD442FC33F7837 ON ujm_interaction_graphic (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_coords (
                id INTEGER NOT NULL, 
                interaction_graphic_id INTEGER DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                shape VARCHAR(255) NOT NULL, 
                color VARCHAR(255) NOT NULL, 
                score_coords DOUBLE PRECISION NOT NULL, 
                size DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CD7B49827876D500 ON ujm_coords (interaction_graphic_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_qcm (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                type_qcm_id INTEGER DEFAULT NULL, 
                shuffle BOOLEAN DEFAULT NULL, 
                score_right_response DOUBLE PRECISION DEFAULT NULL, 
                score_false_response DOUBLE PRECISION DEFAULT NULL, 
                weight_response BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_58C3D5A1886DEE8F ON ujm_interaction_qcm (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_58C3D5A1DCB52A9E ON ujm_interaction_qcm (type_qcm_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_question (
                id INTEGER NOT NULL, 
                expertise_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                category_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                date_create DATETIME NOT NULL, 
                date_modify DATETIME DEFAULT NULL, 
                locked BOOLEAN DEFAULT NULL, 
                model BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F606977A76ED395 ON ujm_question (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F60697712469DE2 ON ujm_question (category_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document_question (
                question_id INTEGER NOT NULL, 
                document_id INTEGER NOT NULL, 
                PRIMARY KEY(question_id, document_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_52D4A3F11E27F6BF ON ujm_document_question (question_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_52D4A3F1C33F7837 ON ujm_document_question (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction (
                id INTEGER NOT NULL, 
                question_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                invite CLOB NOT NULL, 
                ordre INTEGER DEFAULT NULL, 
                feedback CLOB DEFAULT NULL, 
                locked_expertise BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E7D801641E27F6BF ON ujm_interaction (question_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_document_interaction (
                interaction_id INTEGER NOT NULL, 
                document_id INTEGER NOT NULL, 
                PRIMARY KEY(interaction_id, document_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FF792E7A886DEE8F ON ujm_document_interaction (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FF792E7AC33F7837 ON ujm_document_interaction (document_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_expertise (
                id INTEGER NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_expertise_user (
                expertise_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(expertise_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F60D61B9D5B92F9 ON ujm_expertise_user (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F60D61BA76ED395 ON ujm_expertise_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_hint (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B5FFCBE7886DEE8F ON ujm_hint (interaction_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_paper (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                exercise_id INTEGER DEFAULT NULL, 
                num_paper INTEGER NOT NULL, 
                start DATETIME NOT NULL, 
                \"end\" DATETIME DEFAULT NULL, 
                ordre_question CLOB DEFAULT NULL, 
                archive BOOLEAN DEFAULT NULL, 
                date_archive DATE DEFAULT NULL, 
                interupt BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_82972E4BA76ED395 ON ujm_paper (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82972E4BE934951A ON ujm_paper (exercise_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_type_open_question (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_share (
                user_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                allowToModify BOOLEAN NOT NULL, 
                PRIMARY KEY(user_id, question_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_238BD307A76ED395 ON ujm_share (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_238BD3071E27F6BF ON ujm_share (question_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE ujm_exercise_question
        ");
        $this->addSql("
            DROP TABLE ujm_link_hint_paper
        ");
        $this->addSql("
            DROP TABLE ujm_choice
        ");
        $this->addSql("
            DROP TABLE ujm_subscription
        ");
        $this->addSql("
            DROP TABLE ujm_planning
        ");
        $this->addSql("
            DROP TABLE ujm_category
        ");
        $this->addSql("
            DROP TABLE ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_hole
        ");
        $this->addSql("
            DROP TABLE ujm_document
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_hole
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_open
        ");
        $this->addSql("
            DROP TABLE ujm_word_response
        ");
        $this->addSql("
            DROP TABLE ujm_response
        ");
        $this->addSql("
            DROP TABLE ujm_unit
        ");
        $this->addSql("
            DROP TABLE ujm_group
        ");
        $this->addSql("
            DROP TABLE ujm_exercise
        ");
        $this->addSql("
            DROP TABLE ujm_exercise_group
        ");
        $this->addSql("
            DROP TABLE ujm_lock_attempt
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_graphic
        ");
        $this->addSql("
            DROP TABLE ujm_coords
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_question
        ");
        $this->addSql("
            DROP TABLE ujm_document_question
        ");
        $this->addSql("
            DROP TABLE ujm_interaction
        ");
        $this->addSql("
            DROP TABLE ujm_document_interaction
        ");
        $this->addSql("
            DROP TABLE ujm_expertise
        ");
        $this->addSql("
            DROP TABLE ujm_expertise_user
        ");
        $this->addSql("
            DROP TABLE ujm_hint
        ");
        $this->addSql("
            DROP TABLE ujm_paper
        ");
        $this->addSql("
            DROP TABLE ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE ujm_share
        ");
    }
}