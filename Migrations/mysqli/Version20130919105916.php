<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

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
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT NOT NULL, 
                occurrence SMALLINT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                period VARCHAR(255) NOT NULL, 
                periodDuration SMALLINT NOT NULL, 
                INDEX IDX_805FCB8FF7A2C2FC (badge_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD automatic_award TINYINT(1) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP automatic_award
        ");
    }
}