<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

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
            DROP FOREIGN KEY FK_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70 ON claro_widget_display
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD is_admin TINYINT(1) NOT NULL, 
            DROP parent_id, 
            DROP is_locked, 
            DROP is_visible, 
            DROP is_desktop
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD parent_id INT DEFAULT NULL, 
            ADD is_visible TINYINT(1) NOT NULL, 
            ADD is_desktop TINYINT(1) NOT NULL, 
            CHANGE is_admin is_locked TINYINT(1) NOT NULL
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