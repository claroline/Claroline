<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/09 09:33:44
 */
class Version20190909093340 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace CHANGE archived archived TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_workspace SET archived = 0 WHERE archived IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            ALTER TABLE claro_widget_container 
            DROP FOREIGN KEY FK_3B06DD75B628319
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container DROP INDEX idx_3b06dd75b628319
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container 
            ADD CONSTRAINT FK_3B06DD75CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3B06DD75CCE862F ON claro_widget_container (hometab_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace CHANGE archived archived TINYINT(1) DEFAULT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_widget_container 
            DROP FOREIGN KEY FK_3B06DD75CCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container DROP INDEX IDX_3B06DD75CCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container 
            ADD CONSTRAINT FK_3B06DD75CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX idx_3b06dd75b628319 ON claro_widget_container (hometab_id)
        ');
    }
}
