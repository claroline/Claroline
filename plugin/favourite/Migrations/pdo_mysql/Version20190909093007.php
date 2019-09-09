<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/09 09:30:12
 */
class Version20190909093007 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            DROP FOREIGN KEY FK_55DB04521BAD783F
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            DROP FOREIGN KEY FK_55DB0452A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite CHANGE resource_node_id resource_node_id INT DEFAULT NULL
        ');
        $this->addSql('
            DROP INDEX idx_55db0452a76ed395 ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE INDEX IDX_5ED1A9BDA76ED395 ON claro_resource_favourite (user_id)
        ');
        $this->addSql('
            DROP INDEX idx_55db04521bad783f ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE INDEX IDX_5ED1A9BD1BAD783F ON claro_resource_favourite (resource_node_id)
        ');
        $this->addSql('
            DROP INDEX uniq_55db0452a76ed3951bad783f ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5ED1A9BDA76ED3951BAD783F ON claro_resource_favourite (user_id, resource_node_id)
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            DROP FOREIGN KEY FK_5ED1A9BDA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            DROP FOREIGN KEY FK_5ED1A9BD1BAD783F
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite CHANGE resource_node_id resource_node_id INT NOT NULL
        ');
        $this->addSql('
            DROP INDEX uniq_5ed1a9bda76ed3951bad783f ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F ON claro_resource_favourite (user_id, resource_node_id)
        ');
        $this->addSql('
            DROP INDEX idx_5ed1a9bda76ed395 ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE INDEX IDX_55DB0452A76ED395 ON claro_resource_favourite (user_id)
        ');
        $this->addSql('
            DROP INDEX idx_5ed1a9bd1bad783f ON claro_resource_favourite
        ');
        $this->addSql('
            CREATE INDEX IDX_55DB04521BAD783F ON claro_resource_favourite (resource_node_id)
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
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }
}
