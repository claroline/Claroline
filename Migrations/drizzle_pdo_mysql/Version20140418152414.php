<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/18 03:24:15
 */
class Version20140418152414 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_admin_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_C10C14EC5E237E06 (name), 
                INDEX IDX_C10C14ECEC942BCF (plugin_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_admin_tool_role (
                admintool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                PRIMARY KEY(admintool_id, role_id), 
                INDEX IDX_940800692B80F4B6 (admintool_id), 
                INDEX IDX_94080069D60322AC (role_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_admin_tools 
            ADD CONSTRAINT FK_C10C14ECEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_admin_tool_role 
            ADD CONSTRAINT FK_940800692B80F4B6 FOREIGN KEY (admintool_id) 
            REFERENCES claro_admin_tools (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_admin_tool_role 
            ADD CONSTRAINT FK_94080069D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_940800692B80F4B6
        ");
        $this->addSql("
            DROP TABLE claro_admin_tools
        ");
        $this->addSql("
            DROP TABLE claro_admin_tool_role
        ");
    }
}