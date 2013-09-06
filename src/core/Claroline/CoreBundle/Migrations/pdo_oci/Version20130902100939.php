<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/02 10:09:39
 */
class Version20130902100939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD (
                type VARCHAR2(255) NOT NULL, 
                is_visible NUMBER(1) NOT NULL, 
                is_locked NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP (type, is_visible, is_locked)
        ");
    }
}