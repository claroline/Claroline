<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/10/18 09:33:56
 */
final class Version20241018093354 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD evidence_added_by INT DEFAULT NULL, 
            ADD evidence_added_at DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FEEE7B114B FOREIGN KEY (evidence_added_by) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FEEE7B114B ON claro_cursusbundle_presence_status (evidence_added_by)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FEEE7B114B
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FEEE7B114B ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP evidence_added_by, 
            DROP evidence_added_at
        ');
    }
}
