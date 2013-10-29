<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/20 08:07:28
 */
class Version20131020200726 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_favourite (
                id INT IDENTITY NOT NULL, 
                workspace_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_711A30B82D40A1F ON claro_workspace_favourite (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_711A30BA76ED395 ON claro_workspace_favourite (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX workspace_favourite_unique_combination ON claro_workspace_favourite (workspace_id, user_id) 
            WHERE workspace_id IS NOT NULL 
            AND user_id IS NOT NULL
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
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD result NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD resultComparison SMALLINT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_favourite
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN result
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN resultComparison
        ");
    }
}