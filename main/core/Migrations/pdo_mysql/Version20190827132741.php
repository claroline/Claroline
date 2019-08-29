<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/27 01:27:42
 */
class Version20190827132741 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_shortcuts (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                shortcuts_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_872A8149D17F50A6 (uuid), 
                INDEX IDX_872A814982D40A1F (workspace_id), 
                INDEX IDX_872A8149D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            ADD CONSTRAINT FK_872A814982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            ADD CONSTRAINT FK_872A8149D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_workspace_shortcuts
        ');
    }
}
