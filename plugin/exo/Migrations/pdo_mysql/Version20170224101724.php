<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/02/24 10:17:26
 */
class Version20170224101724 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_item_content (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                content_data LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_F725D00B1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_item_content 
            ADD CONSTRAINT FK_F725D00B1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_question CHANGE invite invite LONGTEXT DEFAULT NULL, 
            CHANGE scoreRule scoreRule LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            DROP FOREIGN KEY FK_36819691E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            ADD CONSTRAINT FK_36819691E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F41E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C71E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE ujm_item_content
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F1E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C71E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F41E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question CHANGE invite invite LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE scoreRule scoreRule LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            DROP FOREIGN KEY FK_36819691E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_question_pair 
            ADD CONSTRAINT FK_36819691E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
    }
}
