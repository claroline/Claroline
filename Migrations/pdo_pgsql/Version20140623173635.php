<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 05:36:36
 */
class Version20140623173635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id SERIAL NOT NULL, 
                role_id INT NOT NULL, 
                baseData BOOLEAN NOT NULL, 
                mail BOOLEAN NOT NULL, 
                phone BOOLEAN NOT NULL, 
                sendMail BOOLEAN NOT NULL, 
                sendMessage BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN last_date TO evaluation_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN last_date TO lastest_evaluation_date
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN lastest_evaluation_date TO last_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN evaluation_date TO last_date
        ");
    }
}