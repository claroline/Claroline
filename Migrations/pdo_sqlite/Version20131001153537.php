<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/01 03:35:38
 */
class Version20131001153537 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2excludedResourceNode (
                id INTEGER NOT NULL,
                step_id INTEGER DEFAULT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_867AC78073B21E9C ON innova_step2excludedResourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_867AC780B87FAB32 ON innova_step2excludedResourceNode (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2excludedResourceNode
        ");
    }
}
