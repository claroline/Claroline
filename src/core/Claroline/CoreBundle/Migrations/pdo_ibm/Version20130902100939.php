<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/02 10:09:40
 */
class Version20130902100939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD COLUMN \"type\" VARCHAR(255) NOT NULL 
            ADD COLUMN is_visible SMALLINT NOT NULL 
            ADD COLUMN is_locked SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP COLUMN \"type\" 
            DROP COLUMN is_visible 
            DROP COLUMN is_locked
        ");
    }
}