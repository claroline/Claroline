<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/07 04:12:42
 */
class Version20150907161241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_question 
            DROP FOREIGN KEY FK_2F6069779D5B92F9
        ");
        $this->addSql("
            DROP INDEX IDX_2F6069779D5B92F9 ON ujm_question
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            DROP expertise_id
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction 
            DROP locked_expertise
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction 
            ADD locked_expertise TINYINT(1) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            ADD expertise_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_question 
            ADD CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id) 
            REFERENCES ujm_expertise (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ");
    }
}