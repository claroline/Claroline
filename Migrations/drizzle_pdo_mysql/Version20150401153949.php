<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 03:39:52
 */
class Version20150401153949 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_home_tab_roles (
                hometab_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B81359F3CCE862F (hometab_id), 
                INDEX IDX_B81359F3D60322AC (role_id), 
                PRIMARY KEY(hometab_id, role_id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_home_tab_roles
        ");
    }
}