<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/10/14 08:40:17
 */
final class Version20241014084016 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD presence_updated_by INT DEFAULT NULL, 
            ADD presence_updated_at DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FE349A94C7 FOREIGN KEY (presence_updated_by) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FE349A94C7 ON claro_cursusbundle_presence_status (presence_updated_by)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FE349A94C7
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FE349A94C7 ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP presence_updated_by, 
            DROP presence_updated_at
        ');
    }
}
