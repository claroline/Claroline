<?php

namespace HeVinci\FavouriteBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:39:48
 */
class Version20190909093007 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_resource_favourite (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                resource_node_id INT DEFAULT NULL, 
                INDEX IDX_5ED1A9BDA76ED395 (user_id), 
                INDEX IDX_5ED1A9BD1BAD783F (resource_node_id), 
                UNIQUE INDEX UNIQ_5ED1A9BDA76ED3951BAD783F (user_id, resource_node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_favourite (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_711A30BA76ED395 (user_id), 
                INDEX IDX_711A30B82D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_711A30B82D40A1FA76ED395 (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_5ED1A9BDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_5ED1A9BD1BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_resource_favourite
        ');
        $this->addSql('
            DROP TABLE claro_workspace_favourite
        ');
    }
}
