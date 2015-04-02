<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 03:39:51
 */
class Version20150401153949 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_home_tab_roles (
                hometab_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(hometab_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B81359F3CCE862F ON claro_home_tab_roles (hometab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B81359F3D60322AC ON claro_home_tab_roles (role_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_home_tab_roles
        ");
    }
}