<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/08/07 09:21:29
 */
class Version20150807092127 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD workspace_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C3382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_27921C3382D40A1F ON claro_cursusbundle_cursus (workspace_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP FOREIGN KEY FK_27921C3382D40A1F
        ');
        $this->addSql('
            DROP INDEX IDX_27921C3382D40A1F ON claro_cursusbundle_cursus
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP workspace_id
        ');
    }
}
