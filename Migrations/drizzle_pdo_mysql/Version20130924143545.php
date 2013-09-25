<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/24 02:35:47
 */
class Version20130924143545 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config CHANGE widget_id widget_instance_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E44BF891
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config CHANGE widget_instance_id widget_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23EFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config (widget_id)
        ");
    }
}