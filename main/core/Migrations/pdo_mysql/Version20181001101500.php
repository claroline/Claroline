<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/01 10:15:02
 */
class Version20181001101500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_log_connect_workspace (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                workspace_name VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8724810ED17F50A6 (uuid), 
                INDEX IDX_8724810E82D40A1F (workspace_id), 
                INDEX IDX_8724810EA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                tool_name VARCHAR(255) NOT NULL, 
                original_tool_name VARCHAR(255) NOT NULL, 
                workspace_name VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DDD8A470D17F50A6 (uuid), 
                INDEX IDX_DDD8A4708F7B22CC (tool_id), 
                INDEX IDX_DDD8A470A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_platform (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_897DE045D17F50A6 (uuid), 
                INDEX IDX_897DE045A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_admin_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                tool_name VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_83338977D17F50A6 (uuid), 
                INDEX IDX_833389778F7B22CC (tool_id), 
                INDEX IDX_83338977A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resource_name VARCHAR(255) NOT NULL, 
                resource_type VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CBEC498D17F50A6 (uuid), 
                INDEX IDX_CBEC49889329D25 (resource_id), 
                INDEX IDX_CBEC498A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            ADD CONSTRAINT FK_8724810E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            ADD CONSTRAINT FK_8724810EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            ADD CONSTRAINT FK_DDD8A4708F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_ordered_tool (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            ADD CONSTRAINT FK_DDD8A470A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_platform 
            ADD CONSTRAINT FK_897DE045A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            ADD CONSTRAINT FK_833389778F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_admin_tools (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            ADD CONSTRAINT FK_83338977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            ADD CONSTRAINT FK_CBEC49889329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            ADD CONSTRAINT FK_CBEC498A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_log_connect_workspace
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_platform
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_admin_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_resource
        ');
    }
}
