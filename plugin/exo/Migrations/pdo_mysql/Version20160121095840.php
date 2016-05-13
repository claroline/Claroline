<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/01/21 09:58:42
 */
class Version20160121095840 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP FOREIGN KEY FK_2F6069779D5B92F9
        ');
        $this->addSql('
            DROP INDEX IDX_2F6069779D5B92F9 ON ujm_question
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD type VARCHAR(255) NOT NULL, 
            ADD invite LONGTEXT NOT NULL, 
            ADD feedback LONGTEXT DEFAULT NULL, 
            DROP expertise_id, 
            CHANGE title title VARCHAR(255) NOT NULL, 
            CHANGE locked locked TINYINT(1) NOT NULL, 
            CHANGE model model TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C7886DEE8F
        ');
        $this->addSql('
            DROP INDEX UNIQ_AC9801C7886DEE8F ON ujm_interaction_matching
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching CHANGE shuffle shuffle TINYINT(1) NOT NULL, 
            CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_AC9801C71E27F6BF ON ujm_interaction_matching (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC2886DEE8F
        ');
        $this->addSql('
            DROP INDEX IDX_A7EC2BC2886DEE8F ON ujm_response
        ');
        $this->addSql('
            ALTER TABLE ujm_response CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC21E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_A7EC2BC21E27F6BF ON ujm_response (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP date_create, 
            DROP nb_question_page, 
            DROP start_date, 
            DROP use_date_end, 
            DROP end_date
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A1886DEE8F
        ');
        $this->addSql('
            DROP INDEX UNIQ_58C3D5A1886DEE8F ON ujm_interaction_qcm
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm CHANGE shuffle shuffle TINYINT(1) NOT NULL, 
            CHANGE weight_response weight_response TINYINT(1) NOT NULL, 
            CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_58C3D5A11E27F6BF ON ujm_interaction_qcm (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F886DEE8F
        ');
        $this->addSql('
            DROP INDEX UNIQ_9EBD442F886DEE8F ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_9EBD442F1E27F6BF ON ujm_interaction_graphic (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            DROP FOREIGN KEY FK_B5FFCBE7886DEE8F
        ');
        $this->addSql('
            DROP INDEX IDX_B5FFCBE7886DEE8F ON ujm_hint
        ');
        $this->addSql('
            ALTER TABLE ujm_hint CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE71E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B5FFCBE71E27F6BF ON ujm_hint (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F4886DEE8F
        ');
        $this->addSql('
            DROP INDEX UNIQ_BFFE44F4886DEE8F ON ujm_interaction_open
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_BFFE44F41E27F6BF ON ujm_interaction_open (question_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC1886DEE8F
        ');
        $this->addSql('
            DROP INDEX UNIQ_7343FAC1886DEE8F ON ujm_interaction_hole
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole CHANGE interaction_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC11E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_7343FAC11E27F6BF ON ujm_interaction_hole (question_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD date_create DATETIME NOT NULL, 
            ADD nb_question_page INT NOT NULL, 
            ADD start_date DATETIME NOT NULL, 
            ADD use_date_end TINYINT(1) DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            DROP FOREIGN KEY FK_B5FFCBE71E27F6BF
        ');
        $this->addSql('
            DROP INDEX IDX_B5FFCBE71E27F6BF ON ujm_hint
        ');
        $this->addSql('
            ALTER TABLE ujm_hint CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE7886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B5FFCBE7886DEE8F ON ujm_hint (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442F1E27F6BF
        ');
        $this->addSql('
            DROP INDEX UNIQ_9EBD442F1E27F6BF ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442F886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_9EBD442F886DEE8F ON ujm_interaction_graphic (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            DROP FOREIGN KEY FK_7343FAC11E27F6BF
        ');
        $this->addSql('
            DROP INDEX UNIQ_7343FAC11E27F6BF ON ujm_interaction_hole
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_hole 
            ADD CONSTRAINT FK_7343FAC1886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_7343FAC1886DEE8F ON ujm_interaction_hole (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C71E27F6BF
        ');
        $this->addSql('
            DROP INDEX UNIQ_AC9801C71E27F6BF ON ujm_interaction_matching
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching CHANGE shuffle shuffle TINYINT(1) DEFAULT NULL, 
            CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C7886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_AC9801C7886DEE8F ON ujm_interaction_matching (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP FOREIGN KEY FK_BFFE44F41E27F6BF
        ');
        $this->addSql('
            DROP INDEX UNIQ_BFFE44F41E27F6BF ON ujm_interaction_open
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD CONSTRAINT FK_BFFE44F4886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_BFFE44F4886DEE8F ON ujm_interaction_open (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP FOREIGN KEY FK_58C3D5A11E27F6BF
        ');
        $this->addSql('
            DROP INDEX UNIQ_58C3D5A11E27F6BF ON ujm_interaction_qcm
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm CHANGE shuffle shuffle TINYINT(1) DEFAULT NULL, 
            CHANGE weight_response weight_response TINYINT(1) DEFAULT NULL, 
            CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD CONSTRAINT FK_58C3D5A1886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_58C3D5A1886DEE8F ON ujm_interaction_qcm (interaction_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD expertise_id INT DEFAULT NULL, 
            DROP type, 
            DROP invite, 
            DROP feedback, 
            CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            CHANGE locked locked TINYINT(1) DEFAULT NULL, 
            CHANGE model model TINYINT(1) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id) 
            REFERENCES ujm_expertise (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC21E27F6BF
        ');
        $this->addSql('
            DROP INDEX IDX_A7EC2BC21E27F6BF ON ujm_response
        ');
        $this->addSql('
            ALTER TABLE ujm_response CHANGE question_id interaction_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_A7EC2BC2886DEE8F ON ujm_response (interaction_id)
        ');
    }
}
