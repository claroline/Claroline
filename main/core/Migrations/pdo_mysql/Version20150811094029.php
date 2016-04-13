<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/08/11 09:40:31
 */
class Version20150811094029 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_role_options (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_56C6D283D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_options (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_D603AE0582D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_role_options 
            ADD CONSTRAINT FK_56C6D283D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            ADD CONSTRAINT FK_D603AE0582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD options_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D90285453ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES claro_workspace_options (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D90285453ADB05F1 ON claro_workspace (options_id)
        ');
        $this->addSql("
            ALTER TABLE claro_user_options 
            ADD details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D90285453ADB05F1
        ');
        $this->addSql('
            DROP TABLE claro_role_options
        ');
        $this->addSql('
            DROP TABLE claro_workspace_options
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            DROP details
        ');
        $this->addSql('
            DROP INDEX UNIQ_D90285453ADB05F1 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP options_id
        ');
    }
}
