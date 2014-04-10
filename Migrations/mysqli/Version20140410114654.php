<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 11:46:56
 */
class Version20140410114654 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_admin_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_C10C14EC5E237E06 (name), 
                INDEX IDX_C10C14ECEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_admin_tool_role (
                tool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_940800698F7B22CC (tool_id), 
                INDEX IDX_94080069D60322AC (role_id), 
                PRIMARY KEY(tool_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_admin_tools 
            ADD CONSTRAINT FK_C10C14ECEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_admin_tool_role 
            ADD CONSTRAINT FK_940800698F7B22CC FOREIGN KEY (tool_id) 
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
            DROP FOREIGN KEY FK_940800698F7B22CC
        ");
        $this->addSql("
            DROP TABLE claro_admin_tools
        ");
        $this->addSql("
            DROP TABLE claro_admin_tool_role
        ");
    }
}