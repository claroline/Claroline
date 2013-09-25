<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 06:44:25
 */
class Version20130925184425 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE Step2ExcludedResource (
                id INTEGER NOT NULL,
                step_id INTEGER DEFAULT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E173B21E9C ON Step2ExcludedResource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E1B87FAB32 ON Step2ExcludedResource (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE Step2ExcludedResource
        ");
    }
}
