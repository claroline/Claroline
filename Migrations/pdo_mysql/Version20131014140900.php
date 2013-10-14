<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/14 02:09:00
 */
class Version20131014140900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_favourite (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_711A30B82D40A1F (workspace_id), 
                INDEX IDX_711A30BA76ED395 (user_id), 
                UNIQUE INDEX workspace_favourite_unique_combination (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_favourite
        ");
    }
}