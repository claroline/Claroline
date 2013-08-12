<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/12 02:08:54
 */
class Version20130812140853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_resource_node.user_id', 
            'creator_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN creator_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FFA76ED395
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FFA76ED395'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FFA76ED395 ELSE 
            DROP INDEX IDX_A76799FFA76ED395 ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_resource_node.creator_id', 
            'user_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN user_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF61220EA6
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FF61220EA6'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FF61220EA6 ELSE 
            DROP INDEX IDX_A76799FF61220EA6 ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FFA76ED395 ON claro_resource_node (user_id)
        ");
    }
}