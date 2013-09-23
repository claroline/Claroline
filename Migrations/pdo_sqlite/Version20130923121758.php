<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 12:17:59
 */
class Version20130923121758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resourceOrder INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                path_id INTEGER NOT NULL, 
                status INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
    }
}