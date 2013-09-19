<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 10:59:19
 */
class Version20130919105916 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INT IDENTITY NOT NULL, 
                badge_id INT NOT NULL, 
                occurrence SMALLINT NOT NULL, 
                action NVARCHAR(255) NOT NULL, 
                period NVARCHAR(255) NOT NULL, 
                periodDuration SMALLINT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD automatic_award BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN automatic_award
        ");
    }
}