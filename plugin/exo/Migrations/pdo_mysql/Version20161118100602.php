<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/18 10:06:28
 */
class Version20161118100602 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Updates papers
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD uuid VARCHAR(36) NOT NULL,
            DROP archive, 
            DROP date_archive
        ');
        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_paper SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_82972E4BD17F50A6 ON ujm_paper (uuid)
        ');

        // Updates answers
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC2E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC21E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC21E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        // Downgrades papers
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD archive TINYINT(1) DEFAULT NULL, 
            ADD date_archive DATE DEFAULT NULL,
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_82972E4BD17F50A6 ON ujm_paper
        ');

        // Downgrades answers
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC2E6758861
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC2E6758861 FOREIGN KEY (paper_id) 
            REFERENCES ujm_paper (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC21E27F6BF
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC21E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ');
    }
}
