<?php

namespace Claroline\CoreBundle\Migrations\oci8;

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
            ADD (
                is_admin NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP (
                parent_id, is_locked, is_visible, 
                is_desktop
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP CONSTRAINT FK_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD (
                parent_id NUMBER(10) DEFAULT NULL, 
                is_visible NUMBER(1) NOT NULL, 
                is_desktop NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display RENAME COLUMN is_admin TO is_locked
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