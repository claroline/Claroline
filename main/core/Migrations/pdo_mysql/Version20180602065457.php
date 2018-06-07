<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/02 06:55:01
 */
class Version20180602065457 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_menu_action 
            ADD plugin_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52BEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_1F57E52BEC942BCF ON claro_menu_action (plugin_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP FOREIGN KEY FK_1F57E52BEC942BCF
        ');
        $this->addSql('
            DROP INDEX IDX_1F57E52BEC942BCF ON claro_menu_action
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP plugin_id
        ');
    }
}
