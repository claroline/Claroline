<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/07/26 07:33:28
 */
class Version20190726073324 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30B82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite CHANGE user_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            DROP INDEX workspace_favourite_unique_combination ON claro_workspace_favourite
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_711A30B82D40A1FA76ED395 ON claro_workspace_favourite (workspace_id, user_id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            RENAME TABLE hevinci_favourite TO claro_resource_favourite
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            RENAME TABLE claro_resource_favourite TO hevinci_favourite
        ');

        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30B82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite CHANGE user_id user_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP INDEX uniq_711a30b82d40a1fa76ed395 ON claro_workspace_favourite
        ');
        $this->addSql('
            CREATE UNIQUE INDEX workspace_favourite_unique_combination ON claro_workspace_favourite (workspace_id, user_id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }
}
