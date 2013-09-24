<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 02:56:57
 */
class Version20130920145655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD COLUMN is_admin SMALLINT NOT NULL 
            DROP COLUMN parent_id 
            DROP COLUMN is_locked 
            DROP COLUMN is_visible 
            DROP COLUMN is_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD COLUMN parent_id INTEGER DEFAULT NULL 
            ADD COLUMN is_visible SMALLINT NOT NULL 
            ADD COLUMN is_desktop SMALLINT NOT NULL RENAME is_admin TO is_locked
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB3727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_widget_display (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3727ACA70 ON claro_widget_display (parent_id)
        ");
    }
}