<?php

namespace Claroline\HistoryBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:49:11
 */
class Version20191016124432 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_resource_recent (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                resource_id INT NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                INDEX IDX_544B72FE89329D25 (resource_id), 
                INDEX user_idx (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_recent (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                INDEX IDX_ED82CA3B82D40A1F (workspace_id), 
                INDEX user_idx (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            ADD CONSTRAINT FK_544B72FEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            ADD CONSTRAINT FK_544B72FE89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            ADD CONSTRAINT FK_ED82CA3BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            ADD CONSTRAINT FK_ED82CA3B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_resource_recent
        ');
        $this->addSql('
            DROP TABLE claro_workspace_recent
        ');
    }
}
