<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/24 03:16:12
 */
class Version20161124151551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP type
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
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC11E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
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
    }

    public function down(Schema $schema)
    {
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
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C71E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C71E27F6BF FOREIGN KEY (question_id) 
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
            ALTER TABLE ujm_question 
            ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
