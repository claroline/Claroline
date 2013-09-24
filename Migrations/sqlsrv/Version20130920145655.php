<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
            ADD is_admin BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP COLUMN is_locked
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP COLUMN is_visible
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP COLUMN is_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP CONSTRAINT FK_2D34DB3727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_2D34DB3727ACA70'
            ) 
            ALTER TABLE claro_widget_display 
            DROP CONSTRAINT IDX_2D34DB3727ACA70 ELSE 
            DROP INDEX IDX_2D34DB3727ACA70 ON claro_widget_display
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_widget_display.is_admin', 
            'is_locked', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD is_visible BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD is_desktop BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display ALTER COLUMN is_locked BIT NOT NULL
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