<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/13 12:48:12
 */
class Version20170113124810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP FOREIGN KEY FK_A7EC2BC21E27F6BF
        ');
        $this->addSql('
            DROP INDEX IDX_A7EC2BC21E27F6BF ON ujm_response
        ');
        $this->addSql('
            ALTER TABLE ujm_response CHANGE question_id question_id VARCHAR(36) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response CHANGE question_id question_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD CONSTRAINT FK_A7EC2BC21E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_A7EC2BC21E27F6BF ON ujm_response (question_id)
        ');
    }
}
