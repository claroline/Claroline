<?php

namespace Claroline\HistoryBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 02:36:08
 */
final class Version20191016124432 extends AbstractMigration
{
    public function up(Schema $schema): void
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            DROP FOREIGN KEY FK_544B72FEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_recent 
            DROP FOREIGN KEY FK_544B72FE89329D25
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            DROP FOREIGN KEY FK_ED82CA3BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent 
            DROP FOREIGN KEY FK_ED82CA3B82D40A1F
        ');
        $this->addSql('
            DROP TABLE claro_resource_recent
        ');
        $this->addSql('
            DROP TABLE claro_workspace_recent
        ');
    }
}
