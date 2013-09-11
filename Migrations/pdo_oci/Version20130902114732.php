<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/02 11:47:33
 */
class Version20130902114732 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD (
                user_id NUMBER(10) DEFAULT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EA76ED395 ON claro_widget_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E82D40A1F ON claro_widget_home_tab_config (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP (user_id, workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E82D40A1F
        ");
    }
}