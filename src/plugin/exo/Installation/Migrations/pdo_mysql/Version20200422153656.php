<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:31:04
 */
class Version20200422153656 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE ujm_question (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                mime_type VARCHAR(255) NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                invite LONGTEXT DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                date_create DATETIME NOT NULL, 
                date_modify DATETIME DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                scoreRule LONGTEXT DEFAULT NULL, 
                protect_update TINYINT(1) NOT NULL, 
                mandatory TINYINT(1) NOT NULL, 
                expected_answers TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2F606977D17F50A6 (uuid), 
                INDEX IDX_2F606977A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_hint (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                data LONGTEXT DEFAULT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B5FFCBE7D17F50A6 (uuid), 
                INDEX IDX_B5FFCBE71E27F6BF (question_id), 
                INDEX IDX_B5FFCBE7B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_object_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                mime_type VARCHAR(255) NOT NULL, 
                object_data LONGTEXT NOT NULL, 
                entity_order INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F91814BFD17F50A6 (uuid), 
                INDEX IDX_F91814BF1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                entity_order INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_B47B5FFC1E27F6BF (question_id), 
                INDEX IDX_B47B5FFCB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE ujm_response (
                id INT AUTO_INCREMENT NOT NULL, 
                paper_id INT DEFAULT NULL, 
                ip VARCHAR(255) NOT NULL, 
                mark DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                nb_tries INT NOT NULL, 
                response LONGTEXT DEFAULT NULL, 
                used_hints LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                question_id VARCHAR(36) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_A7EC2BC2D17F50A6 (uuid), 
                INDEX IDX_A7EC2BC2E6758861 (paper_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE ujm_paper (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                exercise_id INT DEFAULT NULL, 
                num_paper INT NOT NULL, 
                start DATETIME NOT NULL, 
                end DATETIME DEFAULT NULL, 
                ordre_question LONGTEXT DEFAULT NULL, 
                interupt TINYINT(1) DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                total DOUBLE PRECISION DEFAULT NULL, 
                anonymous TINYINT(1) DEFAULT NULL, 
                invalidated TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_82972E4BD17F50A6 (uuid), 
                INDEX IDX_82972E4BA76ED395 (user_id), 
                INDEX IDX_82972E4BE934951A (exercise_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_picture (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                width INT NOT NULL, 
                height INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_88AACC8AD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE ujm_exercise (
                id INT AUTO_INCREMENT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                correction_mode VARCHAR(255) NOT NULL, 
                date_correction DATETIME DEFAULT NULL, 
                mark_mode VARCHAR(255) NOT NULL, 
                interruptible TINYINT(1) NOT NULL, 
                show_overview TINYINT(1) NOT NULL, 
                show_back TINYINT(1) NOT NULL, 
                show_end_page TINYINT(1) NOT NULL, 
                show_end_confirm TINYINT(1) NOT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                intermediate_scores LONGTEXT DEFAULT NULL, 
                attempts_reached_message LONGTEXT DEFAULT NULL, 
                success_message LONGTEXT DEFAULT NULL, 
                failure_message LONGTEXT DEFAULT NULL, 
                end_navigation TINYINT(1) NOT NULL, 
                metadata_visible TINYINT(1) NOT NULL, 
                statistics TINYINT(1) NOT NULL, 
                minimal_correction TINYINT(1) NOT NULL, 
                anonymous TINYINT(1) DEFAULT NULL, 
                show_feedback TINYINT(1) NOT NULL, 
                scoreRule LONGTEXT DEFAULT NULL, 
                success_score DOUBLE PRECISION DEFAULT NULL, 
                numbering VARCHAR(255) NOT NULL, 
                showTitles TINYINT(1) NOT NULL, 
                max_papers INT NOT NULL, 
                all_papers_stats TINYINT(1) DEFAULT '1' NOT NULL, 
                mandatory_questions TINYINT(1) NOT NULL, 
                time_limited TINYINT(1) DEFAULT '0' NOT NULL, 
                progression_displayed TINYINT(1) DEFAULT '1' NOT NULL, 
                answers_editable TINYINT(1) DEFAULT '1' NOT NULL, 
                expected_answers TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                picking VARCHAR(255) NOT NULL, 
                random_order VARCHAR(255) NOT NULL, 
                random_pick VARCHAR(255) NOT NULL, 
                pick LONGTEXT NOT NULL, 
                duration INT NOT NULL, 
                max_attempts INT NOT NULL, 
                max_day_attempts INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_374DF525D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_374DF525B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE ujm_share (
                user_id INT NOT NULL, 
                question_id INT NOT NULL, 
                adminRights TINYINT(1) NOT NULL, 
                INDEX IDX_238BD307A76ED395 (user_id), 
                INDEX IDX_238BD3071E27F6BF (question_id), 
                PRIMARY KEY(user_id, question_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_boolean_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_131D51461E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_qcm (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                multiple TINYINT(1) NOT NULL, 
                numbering VARCHAR(255) NOT NULL, 
                direction VARCHAR(255) NOT NULL, 
                shuffle TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_58C3D5A11E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_hole (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                htmlWithoutValue LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_7343FAC11E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_item_content (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                content_data LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_F725D00B1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_graphic (
                id INT AUTO_INCREMENT NOT NULL, 
                image_id INT DEFAULT NULL, 
                question_id INT DEFAULT NULL, 
                INDEX IDX_9EBD442F3DA5256D (image_id), 
                UNIQUE INDEX UNIQ_9EBD442F1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question_grid (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                sumMode VARCHAR(255) NOT NULL, 
                `rows` INT NOT NULL, 
                columns INT NOT NULL, 
                borderWidth INT NOT NULL, 
                borderColor VARCHAR(255) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                UNIQUE INDEX UNIQ_2412DE371E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_matching (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                shuffle TINYINT(1) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                UNIQUE INDEX UNIQ_AC9801C71E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_open (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                contentType VARCHAR(255) NOT NULL, 
                maxAnswerLength INT NOT NULL, 
                UNIQUE INDEX UNIQ_BFFE44F41E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question_ordering (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                direction VARCHAR(255) NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                UNIQUE INDEX UNIQ_73DB988D1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question_pair (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                shuffle TINYINT(1) NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                UNIQUE INDEX UNIQ_36819691E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question_pair_items (
                question_id INT NOT NULL, 
                item_id INT NOT NULL, 
                INDEX IDX_D5F9CF051E27F6BF (question_id), 
                UNIQUE INDEX UNIQ_D5F9CF05126F525E (item_id), 
                PRIMARY KEY(question_id, item_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                text LONGTEXT NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                tries INT NOT NULL, 
                penalty DOUBLE PRECISION DEFAULT NULL, 
                UNIQUE INDEX UNIQ_7B1E8B31E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_coords (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_graphic_id INT DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                shape VARCHAR(255) NOT NULL, 
                color VARCHAR(255) NOT NULL, 
                size DOUBLE PRECISION NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CD7B4982D17F50A6 (uuid), 
                INDEX IDX_CD7B49827876D500 (interaction_graphic_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_association (
                id INT AUTO_INCREMENT NOT NULL, 
                match_question_id INT DEFAULT NULL, 
                label_id INT DEFAULT NULL, 
                proposal_id INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_2DD0DD0F2CBE8797 (match_question_id), 
                INDEX IDX_2DD0DD0F33B92F39 (label_id), 
                INDEX IDX_2DD0DD0FF4792058 (proposal_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_boolean_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                boolean_question_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                data LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B9216398D17F50A6 (uuid), 
                INDEX IDX_B9216398B87FAB32 (resourceNode_id), 
                INDEX IDX_B921639850B1C2F9 (boolean_question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_cell (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                data LONGTEXT DEFAULT NULL, 
                coordsX INT DEFAULT NULL, 
                coordsY INT DEFAULT NULL, 
                color VARCHAR(255) NOT NULL, 
                background VARCHAR(255) NOT NULL, 
                selector TINYINT(1) NOT NULL, 
                input TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_4ABE4F56D17F50A6 (uuid), 
                INDEX IDX_4ABE4F561E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_cell_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                cell_id INT DEFAULT NULL, 
                response VARCHAR(255) NOT NULL, 
                caseSensitive TINYINT(1) DEFAULT NULL, 
                expected TINYINT(1) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DDCDD709D17F50A6 (uuid), 
                INDEX IDX_DDCDD709CB39D93A (cell_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_qcm_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                data LONGTEXT DEFAULT NULL, 
                expected TINYINT(1) NOT NULL, 
                entity_order INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D4BDFA95D17F50A6 (uuid), 
                INDEX IDX_D4BDFA95B87FAB32 (resourceNode_id), 
                INDEX IDX_D4BDFA959DBF539 (interaction_qcm_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_color (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_selection_id INT DEFAULT NULL, 
                colorCode VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_AADB06B4D17F50A6 (uuid), 
                INDEX IDX_AADB06B43CCAFA48 (interaction_selection_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_color_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                selection_id INT DEFAULT NULL, 
                color_id INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_97921969E48EFE78 (selection_id), 
                INDEX IDX_979219697ADA1FB5 (color_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_grid_item (
                id INT AUTO_INCREMENT NOT NULL, 
                coordsX INT DEFAULT NULL, 
                coordsY INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                data LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_66B59764D17F50A6 (uuid), 
                INDEX IDX_66B59764B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_grid_odd (
                id INT AUTO_INCREMENT NOT NULL, 
                item_id INT DEFAULT NULL, 
                pair_question_id INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_858E80E4126F525E (item_id), 
                INDEX IDX_858E80E4B745DCF (pair_question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_grid_row (
                id INT AUTO_INCREMENT NOT NULL, 
                pair_question_id INT DEFAULT NULL, 
                ordered TINYINT(1) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_F63A28D2B745DCF (pair_question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_grid_row_item (
                row_id INT NOT NULL, 
                item_id INT NOT NULL, 
                entity_order INT NOT NULL, 
                INDEX IDX_BF97D89083A269F2 (row_id), 
                INDEX IDX_BF97D890126F525E (item_id), 
                PRIMARY KEY(row_id, item_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_hole (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_hole_id INT DEFAULT NULL, 
                size INT NOT NULL, 
                selector TINYINT(1) NOT NULL, 
                placeholder VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E9F4F525D17F50A6 (uuid), 
                INDEX IDX_E9F4F52575EBD64D (interaction_hole_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_word_response (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_open_id INT DEFAULT NULL, 
                hole_id INT DEFAULT NULL, 
                response VARCHAR(255) NOT NULL, 
                caseSensitive TINYINT(1) DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_4E1930C598DDBDFD (interaction_open_id), 
                INDEX IDX_4E1930C515ADE12C (hole_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_label (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_matching_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                data LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C22A1EB5D17F50A6 (uuid), 
                INDEX IDX_C22A1EB5FAB79C10 (interaction_matching_id), 
                INDEX IDX_C22A1EB5B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_ordering_item (
                id INT AUTO_INCREMENT NOT NULL, 
                ujm_question_ordering_id INT DEFAULT NULL, 
                position INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                data LONGTEXT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_360C6C62D17F50A6 (uuid), 
                INDEX IDX_360C6C62273546DE (ujm_question_ordering_id), 
                INDEX IDX_360C6C62B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_proposal (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_matching_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                data LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2672B44BD17F50A6 (uuid), 
                INDEX IDX_2672B44BFAB79C10 (interaction_matching_id), 
                INDEX IDX_2672B44BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_selection (
                id INT AUTO_INCREMENT NOT NULL, 
                interation_selection_id INT DEFAULT NULL, 
                begin INT NOT NULL, 
                end INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C93913FFD17F50A6 (uuid), 
                INDEX IDX_C93913FF4EA83EF1 (interation_selection_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_step (
                id INT AUTO_INCREMENT NOT NULL, 
                exercise_id INT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                slug VARCHAR(128) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                picking VARCHAR(255) NOT NULL, 
                random_order VARCHAR(255) NOT NULL, 
                random_pick VARCHAR(255) NOT NULL, 
                pick LONGTEXT NOT NULL, 
                duration INT NOT NULL, 
                max_attempts INT NOT NULL, 
                max_day_attempts INT NOT NULL, 
                UNIQUE INDEX UNIQ_C2803688D17F50A6 (uuid), 
                INDEX IDX_C2803688E934951A (exercise_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_step_question (
                step_id INT NOT NULL, 
                question_id INT NOT NULL, 
                mandatory TINYINT(1) DEFAULT NULL, 
                entity_order INT NOT NULL, 
                INDEX IDX_D22EA1CE73B21E9C (step_id), 
                INDEX IDX_D22EA1CE1E27F6BF (question_id), 
                PRIMARY KEY(step_id, question_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE7B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD CONSTRAINT FK_F91814BF1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_resource 
            ADD CONSTRAINT FK_B47B5FFC1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_resource 
            ADD CONSTRAINT FK_B47B5FFCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD CONSTRAINT FK_82972E4BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD CONSTRAINT FK_82972E4BE934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD CONSTRAINT FK_374DF525B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD307A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            ADD CONSTRAINT FK_238BD3071E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_question 
            ADD CONSTRAINT FK_131D51461E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_item_content 
            ADD CONSTRAINT FK_F725D00B1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F3DA5256D FOREIGN KEY (image_id) 
            REFERENCES ujm_picture (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid 
            ADD CONSTRAINT FK_2412DE371E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_ordering 
            ADD CONSTRAINT FK_73DB988D1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            ADD CONSTRAINT FK_36819691E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair_items 
            ADD CONSTRAINT FK_D5F9CF051E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question_pair (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair_items 
            ADD CONSTRAINT FK_D5F9CF05126F525E FOREIGN KEY (item_id) 
            REFERENCES ujm_grid_item (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_selection 
            ADD CONSTRAINT FK_7B1E8B31E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_coords 
            ADD CONSTRAINT FK_CD7B49827876D500 FOREIGN KEY (interaction_graphic_id) 
            REFERENCES ujm_interaction_graphic (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            ADD CONSTRAINT FK_2DD0DD0F2CBE8797 FOREIGN KEY (match_question_id) 
            REFERENCES ujm_interaction_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            ADD CONSTRAINT FK_2DD0DD0F33B92F39 FOREIGN KEY (label_id) 
            REFERENCES ujm_label (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            ADD CONSTRAINT FK_2DD0DD0FF4792058 FOREIGN KEY (proposal_id) 
            REFERENCES ujm_proposal (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_choice 
            ADD CONSTRAINT FK_B9216398B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_choice 
            ADD CONSTRAINT FK_B921639850B1C2F9 FOREIGN KEY (boolean_question_id) 
            REFERENCES ujm_boolean_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_cell 
            ADD CONSTRAINT FK_4ABE4F561E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question_grid (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_cell_choice 
            ADD CONSTRAINT FK_DDCDD709CB39D93A FOREIGN KEY (cell_id) 
            REFERENCES ujm_cell (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            ADD CONSTRAINT FK_D4BDFA95B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            ADD CONSTRAINT FK_D4BDFA959DBF539 FOREIGN KEY (interaction_qcm_id) 
            REFERENCES ujm_interaction_qcm (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_color 
            ADD CONSTRAINT FK_AADB06B43CCAFA48 FOREIGN KEY (interaction_selection_id) 
            REFERENCES ujm_interaction_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            ADD CONSTRAINT FK_97921969E48EFE78 FOREIGN KEY (selection_id) 
            REFERENCES ujm_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            ADD CONSTRAINT FK_979219697ADA1FB5 FOREIGN KEY (color_id) 
            REFERENCES ujm_color (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_item 
            ADD CONSTRAINT FK_66B59764B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_odd 
            ADD CONSTRAINT FK_858E80E4126F525E FOREIGN KEY (item_id) 
            REFERENCES ujm_grid_item (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_odd 
            ADD CONSTRAINT FK_858E80E4B745DCF FOREIGN KEY (pair_question_id) 
            REFERENCES ujm_question_pair (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row 
            ADD CONSTRAINT FK_F63A28D2B745DCF FOREIGN KEY (pair_question_id) 
            REFERENCES ujm_question_pair (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row_item 
            ADD CONSTRAINT FK_BF97D89083A269F2 FOREIGN KEY (row_id) 
            REFERENCES ujm_grid_row (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row_item 
            ADD CONSTRAINT FK_BF97D890126F525E FOREIGN KEY (item_id) 
            REFERENCES ujm_grid_item (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            ADD CONSTRAINT FK_E9F4F52575EBD64D FOREIGN KEY (interaction_hole_id) 
            REFERENCES ujm_interaction_hole (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response 
            ADD CONSTRAINT FK_4E1930C598DDBDFD FOREIGN KEY (interaction_open_id) 
            REFERENCES ujm_interaction_open (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response 
            ADD CONSTRAINT FK_4E1930C515ADE12C FOREIGN KEY (hole_id) 
            REFERENCES ujm_hole (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            ADD CONSTRAINT FK_C22A1EB5FAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            ADD CONSTRAINT FK_C22A1EB5B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_ordering_item 
            ADD CONSTRAINT FK_360C6C62273546DE FOREIGN KEY (ujm_question_ordering_id) 
            REFERENCES ujm_question_ordering (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_ordering_item 
            ADD CONSTRAINT FK_360C6C62B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_2672B44BFAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_2672B44BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_selection 
            ADD CONSTRAINT FK_C93913FF4EA83EF1 FOREIGN KEY (interation_selection_id) 
            REFERENCES ujm_interaction_selection (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD CONSTRAINT FK_C2803688E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE73B21E9C FOREIGN KEY (step_id) 
            REFERENCES ujm_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_hint 
            DROP FOREIGN KEY FK_B5FFCBE71E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            DROP FOREIGN KEY FK_F91814BF1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_resource 
            DROP FOREIGN KEY FK_B47B5FFC1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_share 
            DROP FOREIGN KEY FK_238BD3071E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_question 
            DROP FOREIGN KEY FK_131D51461E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_item_content 
            DROP FOREIGN KEY FK_F725D00B1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_grid 
            DROP FOREIGN KEY FK_2412DE371E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C71E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F41E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_ordering 
            DROP FOREIGN KEY FK_73DB988D1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            DROP FOREIGN KEY FK_36819691E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_selection 
            DROP FOREIGN KEY FK_7B1E8B31E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            DROP FOREIGN KEY FK_D22EA1CE1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC2E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F3DA5256D
        ');
        $this->addSql('
            ALTER TABLE ujm_paper 
            DROP FOREIGN KEY FK_82972E4BE934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            DROP FOREIGN KEY FK_C2803688E934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_boolean_choice 
            DROP FOREIGN KEY FK_B921639850B1C2F9
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            DROP FOREIGN KEY FK_D4BDFA959DBF539
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            DROP FOREIGN KEY FK_E9F4F52575EBD64D
        ');
        $this->addSql('
            ALTER TABLE ujm_coords 
            DROP FOREIGN KEY FK_CD7B49827876D500
        ');
        $this->addSql('
            ALTER TABLE ujm_cell 
            DROP FOREIGN KEY FK_4ABE4F561E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            DROP FOREIGN KEY FK_2DD0DD0F2CBE8797
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            DROP FOREIGN KEY FK_C22A1EB5FAB79C10
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            DROP FOREIGN KEY FK_2672B44BFAB79C10
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response 
            DROP FOREIGN KEY FK_4E1930C598DDBDFD
        ');
        $this->addSql('
            ALTER TABLE ujm_ordering_item 
            DROP FOREIGN KEY FK_360C6C62273546DE
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair_items 
            DROP FOREIGN KEY FK_D5F9CF051E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_odd 
            DROP FOREIGN KEY FK_858E80E4B745DCF
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row 
            DROP FOREIGN KEY FK_F63A28D2B745DCF
        ');
        $this->addSql('
            ALTER TABLE ujm_color 
            DROP FOREIGN KEY FK_AADB06B43CCAFA48
        ');
        $this->addSql('
            ALTER TABLE ujm_selection 
            DROP FOREIGN KEY FK_C93913FF4EA83EF1
        ');
        $this->addSql('
            ALTER TABLE ujm_cell_choice 
            DROP FOREIGN KEY FK_DDCDD709CB39D93A
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            DROP FOREIGN KEY FK_979219697ADA1FB5
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair_items 
            DROP FOREIGN KEY FK_D5F9CF05126F525E
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_odd 
            DROP FOREIGN KEY FK_858E80E4126F525E
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row_item 
            DROP FOREIGN KEY FK_BF97D890126F525E
        ');
        $this->addSql('
            ALTER TABLE ujm_grid_row_item 
            DROP FOREIGN KEY FK_BF97D89083A269F2
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response 
            DROP FOREIGN KEY FK_4E1930C515ADE12C
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            DROP FOREIGN KEY FK_2DD0DD0F33B92F39
        ');
        $this->addSql('
            ALTER TABLE ujm_association 
            DROP FOREIGN KEY FK_2DD0DD0FF4792058
        ');
        $this->addSql('
            ALTER TABLE ujm_color_selection 
            DROP FOREIGN KEY FK_97921969E48EFE78
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            DROP FOREIGN KEY FK_D22EA1CE73B21E9C
        ');
        $this->addSql('
            DROP TABLE ujm_question
        ');
        $this->addSql('
            DROP TABLE ujm_hint
        ');
        $this->addSql('
            DROP TABLE ujm_object_question
        ');
        $this->addSql('
            DROP TABLE ujm_question_resource
        ');
        $this->addSql('
            DROP TABLE ujm_response
        ');
        $this->addSql('
            DROP TABLE ujm_paper
        ');
        $this->addSql('
            DROP TABLE ujm_picture
        ');
        $this->addSql('
            DROP TABLE ujm_exercise
        ');
        $this->addSql('
            DROP TABLE ujm_share
        ');
        $this->addSql('
            DROP TABLE ujm_boolean_question
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_qcm
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_hole
        ');
        $this->addSql('
            DROP TABLE ujm_item_content
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_graphic
        ');
        $this->addSql('
            DROP TABLE ujm_question_grid
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_matching
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_open
        ');
        $this->addSql('
            DROP TABLE ujm_question_ordering
        ');
        $this->addSql('
            DROP TABLE ujm_question_pair
        ');
        $this->addSql('
            DROP TABLE ujm_question_pair_items
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_selection
        ');
        $this->addSql('
            DROP TABLE ujm_coords
        ');
        $this->addSql('
            DROP TABLE ujm_association
        ');
        $this->addSql('
            DROP TABLE ujm_boolean_choice
        ');
        $this->addSql('
            DROP TABLE ujm_cell
        ');
        $this->addSql('
            DROP TABLE ujm_cell_choice
        ');
        $this->addSql('
            DROP TABLE ujm_choice
        ');
        $this->addSql('
            DROP TABLE ujm_color
        ');
        $this->addSql('
            DROP TABLE ujm_color_selection
        ');
        $this->addSql('
            DROP TABLE ujm_grid_item
        ');
        $this->addSql('
            DROP TABLE ujm_grid_odd
        ');
        $this->addSql('
            DROP TABLE ujm_grid_row
        ');
        $this->addSql('
            DROP TABLE ujm_grid_row_item
        ');
        $this->addSql('
            DROP TABLE ujm_hole
        ');
        $this->addSql('
            DROP TABLE ujm_word_response
        ');
        $this->addSql('
            DROP TABLE ujm_label
        ');
        $this->addSql('
            DROP TABLE ujm_ordering_item
        ');
        $this->addSql('
            DROP TABLE ujm_proposal
        ');
        $this->addSql('
            DROP TABLE ujm_selection
        ');
        $this->addSql('
            DROP TABLE ujm_step
        ');
        $this->addSql('
            DROP TABLE ujm_step_question
        ');
    }
}
