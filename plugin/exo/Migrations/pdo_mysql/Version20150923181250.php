<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/01/21 09:49:22
 */
class Version20150923181250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_type_open_question (
                id INT AUTO_INCREMENT NOT NULL,
                value VARCHAR(255) NOT NULL,
                code INT NOT NULL,
                UNIQUE INDEX UNIQ_ABC1CC4777153098 (code),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_document (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                `label` VARCHAR(255) NOT NULL,
                url VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                INDEX IDX_41FEAA4FA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_category (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                value VARCHAR(255) NOT NULL,
                locker TINYINT(1) NOT NULL,
                INDEX IDX_9FDB39F8A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_hole (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_hole_id INT DEFAULT NULL,
                size INT NOT NULL,
                position INT DEFAULT NULL,
                orthography TINYINT(1) DEFAULT NULL,
                selector TINYINT(1) DEFAULT NULL,
                INDEX IDX_E9F4F52575EBD64D (interaction_hole_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_expertise (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                status VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_expertise_user (
                expertise_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_F60D61B9D5B92F9 (expertise_id),
                INDEX IDX_F60D61BA76ED395 (user_id),
                PRIMARY KEY(expertise_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_question (
                id INT AUTO_INCREMENT NOT NULL,
                expertise_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                category_id INT DEFAULT NULL,
                title VARCHAR(255) DEFAULT NULL,
                description LONGTEXT DEFAULT NULL,
                date_create DATETIME NOT NULL,
                date_modify DATETIME DEFAULT NULL,
                locked TINYINT(1) DEFAULT NULL,
                model TINYINT(1) DEFAULT NULL,
                INDEX IDX_2F6069779D5B92F9 (expertise_id),
                INDEX IDX_2F606977A76ED395 (user_id),
                INDEX IDX_2F60697712469DE2 (category_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_document_question (
                question_id INT NOT NULL,
                document_id INT NOT NULL,
                INDEX IDX_52D4A3F11E27F6BF (question_id),
                INDEX IDX_52D4A3F1C33F7837 (document_id),
                PRIMARY KEY(question_id, document_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_paper (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                exercise_id INT DEFAULT NULL,
                num_paper INT NOT NULL,
                start DATETIME NOT NULL,
                end DATETIME DEFAULT NULL,
                ordre_question LONGTEXT DEFAULT NULL,
                archive TINYINT(1) DEFAULT NULL,
                date_archive DATE DEFAULT NULL,
                interupt TINYINT(1) DEFAULT NULL,
                INDEX IDX_82972E4BA76ED395 (user_id),
                INDEX IDX_82972E4BE934951A (exercise_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_word_response (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_open_id INT DEFAULT NULL,
                hole_id INT DEFAULT NULL,
                response VARCHAR(255) NOT NULL,
                caseSensitive TINYINT(1) DEFAULT NULL,
                score DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                INDEX IDX_4E1930C598DDBDFD (interaction_open_id),
                INDEX IDX_4E1930C515ADE12C (hole_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_lock_attempt (
                id INT AUTO_INCREMENT NOT NULL,
                paper_id INT DEFAULT NULL,
                key_lock VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                INDEX IDX_7A7CDF96E6758861 (paper_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_matching (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                type_matching_id INT DEFAULT NULL,
                shuffle TINYINT(1) DEFAULT NULL,
                UNIQUE INDEX UNIQ_AC9801C7886DEE8F (interaction_id),
                INDEX IDX_AC9801C7F881A129 (type_matching_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_exercise_question (
                exercise_id INT NOT NULL,
                question_id INT NOT NULL,
                ordre INT NOT NULL,
                INDEX IDX_DB79F240E934951A (exercise_id),
                INDEX IDX_DB79F2401E27F6BF (question_id),
                PRIMARY KEY(exercise_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_link_hint_paper (
                hint_id INT NOT NULL,
                paper_id INT NOT NULL,
                view TINYINT(1) NOT NULL,
                INDEX IDX_B76F00F9519161AB (hint_id),
                INDEX IDX_B76F00F9E6758861 (paper_id),
                PRIMARY KEY(hint_id, paper_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_proposal (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_matching_id INT DEFAULT NULL,
                value LONGTEXT NOT NULL,
                position_force TINYINT(1) DEFAULT NULL,
                ordre INT NOT NULL,
                INDEX IDX_2672B44BFAB79C10 (interaction_matching_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_proposal_label (
                proposal_id INT NOT NULL,
                label_id INT NOT NULL,
                INDEX IDX_F9B1BA4AF4792058 (proposal_id),
                INDEX IDX_F9B1BA4A33B92F39 (label_id),
                PRIMARY KEY(proposal_id, label_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_response (
                id INT AUTO_INCREMENT NOT NULL,
                paper_id INT DEFAULT NULL,
                interaction_id INT DEFAULT NULL,
                ip VARCHAR(255) NOT NULL,
                mark DOUBLE PRECISION NOT NULL,
                nb_tries INT NOT NULL,
                response LONGTEXT DEFAULT NULL,
                INDEX IDX_A7EC2BC2E6758861 (paper_id),
                INDEX IDX_A7EC2BC2886DEE8F (interaction_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_subscription (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                exercise_id INT DEFAULT NULL,
                creator TINYINT(1) NOT NULL,
                admin TINYINT(1) NOT NULL,
                INDEX IDX_A17BA225A76ED395 (user_id),
                INDEX IDX_A17BA225E934951A (exercise_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction (
                id INT AUTO_INCREMENT NOT NULL,
                question_id INT DEFAULT NULL,
                type VARCHAR(255) NOT NULL,
                invite LONGTEXT NOT NULL,
                ordre INT DEFAULT NULL,
                feedback LONGTEXT DEFAULT NULL,
                locked_expertise TINYINT(1) DEFAULT NULL,
                INDEX IDX_E7D801641E27F6BF (question_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_document_interaction (
                interaction_id INT NOT NULL,
                document_id INT NOT NULL,
                INDEX IDX_FF792E7A886DEE8F (interaction_id),
                INDEX IDX_FF792E7AC33F7837 (document_id),
                PRIMARY KEY(interaction_id, document_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_unit (
                id INT AUTO_INCREMENT NOT NULL,
                value VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_type_matching (
                id INT AUTO_INCREMENT NOT NULL,
                value VARCHAR(255) NOT NULL,
                code INT NOT NULL,
                UNIQUE INDEX UNIQ_45333F9A77153098 (code),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_label (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_matching_id INT DEFAULT NULL,
                value LONGTEXT NOT NULL,
                score_right_response DOUBLE PRECISION DEFAULT NULL,
                position_force TINYINT(1) DEFAULT NULL,
                ordre INT NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                INDEX IDX_C22A1EB5FAB79C10 (interaction_matching_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_share (
                user_id INT NOT NULL,
                question_id INT NOT NULL,
                allowToModify TINYINT(1) NOT NULL,
                INDEX IDX_238BD307A76ED395 (user_id),
                INDEX IDX_238BD3071E27F6BF (question_id),
                PRIMARY KEY(user_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_coords (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_graphic_id INT DEFAULT NULL,
                value VARCHAR(255) NOT NULL,
                shape VARCHAR(255) NOT NULL,
                color VARCHAR(255) NOT NULL,
                score_coords DOUBLE PRECISION NOT NULL,
                size DOUBLE PRECISION NOT NULL,
                feedback LONGTEXT DEFAULT NULL,
                INDEX IDX_CD7B49827876D500 (interaction_graphic_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_exercise (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                shuffle TINYINT(1) DEFAULT NULL,
                nb_question INT NOT NULL,
                keepSameQuestion TINYINT(1) DEFAULT NULL,
                date_create DATETIME NOT NULL,
                duration INT NOT NULL,
                nb_question_page INT NOT NULL,
                doprint TINYINT(1) DEFAULT NULL,
                max_attempts INT NOT NULL,
                correction_mode VARCHAR(255) NOT NULL,
                date_correction DATETIME DEFAULT NULL,
                mark_mode VARCHAR(255) NOT NULL,
                start_date DATETIME NOT NULL,
                use_date_end TINYINT(1) DEFAULT NULL,
                end_date DATETIME DEFAULT NULL,
                disp_button_interrupt TINYINT(1) DEFAULT NULL,
                lock_attempt TINYINT(1) DEFAULT NULL,
                published TINYINT(1) NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_374DF525B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_exercise_group (
                exercise_id INT NOT NULL,
                group_id INT NOT NULL,
                INDEX IDX_78179004E934951A (exercise_id),
                INDEX IDX_78179004FE54D947 (group_id),
                PRIMARY KEY(exercise_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_planning (
                id INT AUTO_INCREMENT NOT NULL,
                group_id INT DEFAULT NULL,
                start_time DATETIME NOT NULL,
                end_time DATETIME NOT NULL,
                INDEX IDX_4D0E9FCFFE54D947 (group_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_qcm (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                type_qcm_id INT DEFAULT NULL,
                shuffle TINYINT(1) DEFAULT NULL,
                score_right_response DOUBLE PRECISION DEFAULT NULL,
                score_false_response DOUBLE PRECISION DEFAULT NULL,
                weight_response TINYINT(1) DEFAULT NULL,
                UNIQUE INDEX UNIQ_58C3D5A1886DEE8F (interaction_id),
                INDEX IDX_58C3D5A1DCB52A9E (type_qcm_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_graphic (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                document_id INT DEFAULT NULL,
                width INT NOT NULL,
                height INT NOT NULL,
                UNIQUE INDEX UNIQ_9EBD442F886DEE8F (interaction_id),
                INDEX IDX_9EBD442FC33F7837 (document_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_type_qcm (
                id INT AUTO_INCREMENT NOT NULL,
                value VARCHAR(255) NOT NULL,
                code INT NOT NULL,
                UNIQUE INDEX UNIQ_4C21382C77153098 (code),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_choice (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_qcm_id INT DEFAULT NULL,
                `label` LONGTEXT NOT NULL,
                ordre INT NOT NULL,
                weight DOUBLE PRECISION DEFAULT NULL,
                feedback LONGTEXT DEFAULT NULL,
                right_response TINYINT(1) DEFAULT NULL,
                position_force TINYINT(1) DEFAULT NULL,
                INDEX IDX_D4BDFA959DBF539 (interaction_qcm_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_group (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_hint (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                value VARCHAR(255) NOT NULL,
                penalty DOUBLE PRECISION NOT NULL,
                INDEX IDX_B5FFCBE7886DEE8F (interaction_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_open (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                typeopenquestion_id INT DEFAULT NULL,
                orthography_correct TINYINT(1) NOT NULL,
                scoreMaxLongResp DOUBLE PRECISION DEFAULT NULL,
                UNIQUE INDEX UNIQ_BFFE44F4886DEE8F (interaction_id),
                INDEX IDX_BFFE44F46AFD3CF (typeopenquestion_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_interaction_hole (
                id INT AUTO_INCREMENT NOT NULL,
                interaction_id INT DEFAULT NULL,
                html LONGTEXT NOT NULL,
                htmlWithoutValue LONGTEXT DEFAULT NULL,
                UNIQUE INDEX UNIQ_7343FAC1886DEE8F (interaction_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_document
            ADD CONSTRAINT FK_41FEAA4FA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_category
            ADD CONSTRAINT FK_9FDB39F8A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_hole
            ADD CONSTRAINT FK_E9F4F52575EBD64D FOREIGN KEY (interaction_hole_id)
            REFERENCES ujm_interaction_hole (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_expertise_user
            ADD CONSTRAINT FK_F60D61B9D5B92F9 FOREIGN KEY (expertise_id)
            REFERENCES ujm_expertise (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_expertise_user
            ADD CONSTRAINT FK_F60D61BA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            ADD CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id)
            REFERENCES ujm_expertise (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            ADD CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            ADD CONSTRAINT FK_2F60697712469DE2 FOREIGN KEY (category_id)
            REFERENCES ujm_category (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_document_question
            ADD CONSTRAINT FK_52D4A3F11E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_document_question
            ADD CONSTRAINT FK_52D4A3F1C33F7837 FOREIGN KEY (document_id)
            REFERENCES ujm_document (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_paper
            ADD CONSTRAINT FK_82972E4BA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_paper
            ADD CONSTRAINT FK_82972E4BE934951A FOREIGN KEY (exercise_id)
            REFERENCES ujm_exercise (id)
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
            ALTER TABLE ujm_lock_attempt
            ADD CONSTRAINT FK_7A7CDF96E6758861 FOREIGN KEY (paper_id)
            REFERENCES ujm_paper (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching
            ADD CONSTRAINT FK_AC9801C7886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching
            ADD CONSTRAINT FK_AC9801C7F881A129 FOREIGN KEY (type_matching_id)
            REFERENCES ujm_type_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_question
            ADD CONSTRAINT FK_DB79F240E934951A FOREIGN KEY (exercise_id)
            REFERENCES ujm_exercise (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_question
            ADD CONSTRAINT FK_DB79F2401E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_link_hint_paper
            ADD CONSTRAINT FK_B76F00F9519161AB FOREIGN KEY (hint_id)
            REFERENCES ujm_hint (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_link_hint_paper
            ADD CONSTRAINT FK_B76F00F9E6758861 FOREIGN KEY (paper_id)
            REFERENCES ujm_paper (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal
            ADD CONSTRAINT FK_2672B44BFAB79C10 FOREIGN KEY (interaction_matching_id)
            REFERENCES ujm_interaction_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal_label
            ADD CONSTRAINT FK_F9B1BA4AF4792058 FOREIGN KEY (proposal_id)
            REFERENCES ujm_proposal (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal_label
            ADD CONSTRAINT FK_F9B1BA4A33B92F39 FOREIGN KEY (label_id)
            REFERENCES ujm_label (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_response
            ADD CONSTRAINT FK_A7EC2BC2E6758861 FOREIGN KEY (paper_id)
            REFERENCES ujm_paper (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_response
            ADD CONSTRAINT FK_A7EC2BC2886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_subscription
            ADD CONSTRAINT FK_A17BA225A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_subscription
            ADD CONSTRAINT FK_A17BA225E934951A FOREIGN KEY (exercise_id)
            REFERENCES ujm_exercise (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction
            ADD CONSTRAINT FK_E7D801641E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_document_interaction
            ADD CONSTRAINT FK_FF792E7A886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_document_interaction
            ADD CONSTRAINT FK_FF792E7AC33F7837 FOREIGN KEY (document_id)
            REFERENCES ujm_document (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_label
            ADD CONSTRAINT FK_C22A1EB5FAB79C10 FOREIGN KEY (interaction_matching_id)
            REFERENCES ujm_interaction_matching (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_share
            ADD CONSTRAINT FK_238BD307A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_share
            ADD CONSTRAINT FK_238BD3071E27F6BF FOREIGN KEY (question_id)
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_coords
            ADD CONSTRAINT FK_CD7B49827876D500 FOREIGN KEY (interaction_graphic_id)
            REFERENCES ujm_interaction_graphic (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise
            ADD CONSTRAINT FK_374DF525B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_group
            ADD CONSTRAINT FK_78179004E934951A FOREIGN KEY (exercise_id)
            REFERENCES ujm_exercise (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_group
            ADD CONSTRAINT FK_78179004FE54D947 FOREIGN KEY (group_id)
            REFERENCES ujm_group (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_planning
            ADD CONSTRAINT FK_4D0E9FCFFE54D947 FOREIGN KEY (group_id)
            REFERENCES ujm_group (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm
            ADD CONSTRAINT FK_58C3D5A1886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm
            ADD CONSTRAINT FK_58C3D5A1DCB52A9E FOREIGN KEY (type_qcm_id)
            REFERENCES ujm_type_qcm (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic
            ADD CONSTRAINT FK_9EBD442F886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic
            ADD CONSTRAINT FK_9EBD442FC33F7837 FOREIGN KEY (document_id)
            REFERENCES ujm_document (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_choice
            ADD CONSTRAINT FK_D4BDFA959DBF539 FOREIGN KEY (interaction_qcm_id)
            REFERENCES ujm_interaction_qcm (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_hint
            ADD CONSTRAINT FK_B5FFCBE7886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open
            ADD CONSTRAINT FK_BFFE44F4886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open
            ADD CONSTRAINT FK_BFFE44F46AFD3CF FOREIGN KEY (typeopenquestion_id)
            REFERENCES ujm_type_open_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole
            ADD CONSTRAINT FK_7343FAC1886DEE8F FOREIGN KEY (interaction_id)
            REFERENCES ujm_interaction (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open
            DROP FOREIGN KEY FK_BFFE44F46AFD3CF
        ');
        $this->addSql('
            ALTER TABLE ujm_document_question
            DROP FOREIGN KEY FK_52D4A3F1C33F7837
        ');
        $this->addSql('
            ALTER TABLE ujm_document_interaction
            DROP FOREIGN KEY FK_FF792E7AC33F7837
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic
            DROP FOREIGN KEY FK_9EBD442FC33F7837
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            DROP FOREIGN KEY FK_2F60697712469DE2
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response
            DROP FOREIGN KEY FK_4E1930C515ADE12C
        ');
        $this->addSql('
            ALTER TABLE ujm_expertise_user
            DROP FOREIGN KEY FK_F60D61B9D5B92F9
        ');
        $this->addSql('
            ALTER TABLE ujm_question
            DROP FOREIGN KEY FK_2F6069779D5B92F9
        ');
        $this->addSql('
            ALTER TABLE ujm_document_question
            DROP FOREIGN KEY FK_52D4A3F11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_question
            DROP FOREIGN KEY FK_DB79F2401E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction
            DROP FOREIGN KEY FK_E7D801641E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_share
            DROP FOREIGN KEY FK_238BD3071E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_lock_attempt
            DROP FOREIGN KEY FK_7A7CDF96E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_link_hint_paper
            DROP FOREIGN KEY FK_B76F00F9E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_response
            DROP FOREIGN KEY FK_A7EC2BC2E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal
            DROP FOREIGN KEY FK_2672B44BFAB79C10
        ');
        $this->addSql('
            ALTER TABLE ujm_label
            DROP FOREIGN KEY FK_C22A1EB5FAB79C10
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal_label
            DROP FOREIGN KEY FK_F9B1BA4AF4792058
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching
            DROP FOREIGN KEY FK_AC9801C7886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_response
            DROP FOREIGN KEY FK_A7EC2BC2886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_document_interaction
            DROP FOREIGN KEY FK_FF792E7A886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm
            DROP FOREIGN KEY FK_58C3D5A1886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic
            DROP FOREIGN KEY FK_9EBD442F886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_hint
            DROP FOREIGN KEY FK_B5FFCBE7886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open
            DROP FOREIGN KEY FK_BFFE44F4886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole
            DROP FOREIGN KEY FK_7343FAC1886DEE8F
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching
            DROP FOREIGN KEY FK_AC9801C7F881A129
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal_label
            DROP FOREIGN KEY FK_F9B1BA4A33B92F39
        ');
        $this->addSql('
            ALTER TABLE ujm_paper
            DROP FOREIGN KEY FK_82972E4BE934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_question
            DROP FOREIGN KEY FK_DB79F240E934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_subscription
            DROP FOREIGN KEY FK_A17BA225E934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_group
            DROP FOREIGN KEY FK_78179004E934951A
        ');
        $this->addSql('
            ALTER TABLE ujm_choice
            DROP FOREIGN KEY FK_D4BDFA959DBF539
        ');
        $this->addSql('
            ALTER TABLE ujm_coords
            DROP FOREIGN KEY FK_CD7B49827876D500
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm
            DROP FOREIGN KEY FK_58C3D5A1DCB52A9E
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise_group
            DROP FOREIGN KEY FK_78179004FE54D947
        ');
        $this->addSql('
            ALTER TABLE ujm_planning
            DROP FOREIGN KEY FK_4D0E9FCFFE54D947
        ');
        $this->addSql('
            ALTER TABLE ujm_link_hint_paper
            DROP FOREIGN KEY FK_B76F00F9519161AB
        ');
        $this->addSql('
            ALTER TABLE ujm_word_response
            DROP FOREIGN KEY FK_4E1930C598DDBDFD
        ');
        $this->addSql('
            ALTER TABLE ujm_hole
            DROP FOREIGN KEY FK_E9F4F52575EBD64D
        ');
        $this->addSql('
            DROP TABLE ujm_type_open_question
        ');
        $this->addSql('
            DROP TABLE ujm_document
        ');
        $this->addSql('
            DROP TABLE ujm_category
        ');
        $this->addSql('
            DROP TABLE ujm_hole
        ');
        $this->addSql('
            DROP TABLE ujm_expertise
        ');
        $this->addSql('
            DROP TABLE ujm_expertise_user
        ');
        $this->addSql('
            DROP TABLE ujm_question
        ');
        $this->addSql('
            DROP TABLE ujm_document_question
        ');
        $this->addSql('
            DROP TABLE ujm_paper
        ');
        $this->addSql('
            DROP TABLE ujm_word_response
        ');
        $this->addSql('
            DROP TABLE ujm_lock_attempt
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_matching
        ');
        $this->addSql('
            DROP TABLE ujm_exercise_question
        ');
        $this->addSql('
            DROP TABLE ujm_link_hint_paper
        ');
        $this->addSql('
            DROP TABLE ujm_proposal
        ');
        $this->addSql('
            DROP TABLE ujm_proposal_label
        ');
        $this->addSql('
            DROP TABLE ujm_response
        ');
        $this->addSql('
            DROP TABLE ujm_subscription
        ');
        $this->addSql('
            DROP TABLE ujm_interaction
        ');
        $this->addSql('
            DROP TABLE ujm_document_interaction
        ');
        $this->addSql('
            DROP TABLE ujm_unit
        ');
        $this->addSql('
            DROP TABLE ujm_type_matching
        ');
        $this->addSql('
            DROP TABLE ujm_label
        ');
        $this->addSql('
            DROP TABLE ujm_share
        ');
        $this->addSql('
            DROP TABLE ujm_coords
        ');
        $this->addSql('
            DROP TABLE ujm_exercise
        ');
        $this->addSql('
            DROP TABLE ujm_exercise_group
        ');
        $this->addSql('
            DROP TABLE ujm_planning
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_qcm
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_graphic
        ');
        $this->addSql('
            DROP TABLE ujm_type_qcm
        ');
        $this->addSql('
            DROP TABLE ujm_choice
        ');
        $this->addSql('
            DROP TABLE ujm_group
        ');
        $this->addSql('
            DROP TABLE ujm_hint
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_open
        ');
        $this->addSql('
            DROP TABLE ujm_interaction_hole
        ');
    }
}
