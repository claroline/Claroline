<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/09/19 10:58:00
 */
final class Version20240919105759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            DROP FOREIGN KEY FK_B68DD0D5727ACA70
        ');
        $this->addSql('
            DROP INDEX IDX_B68DD0D5727ACA70 ON claro__organization
        ');
        $this->addSql('
            ALTER TABLE claro__organization 
            DROP parent_id, 
            DROP position, 
            DROP lft, 
            DROP lvl, 
            DROP rgt, 
            DROP root
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            ADD parent_id INT DEFAULT NULL, 
            ADD position INT DEFAULT NULL, 
            ADD lft INT NOT NULL, 
            ADD lvl INT NOT NULL, 
            ADD rgt INT NOT NULL, 
            ADD root INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__organization 
            ADD CONSTRAINT FK_B68DD0D5727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro__organization (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_B68DD0D5727ACA70 ON claro__organization (parent_id)
        ');
    }
}
