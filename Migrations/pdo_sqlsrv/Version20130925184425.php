<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL,
                step_id INT,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E173B21E9C ON Step2ExcludedResource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C6F7A1E1B87FAB32 ON Step2ExcludedResource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E173B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE Step2ExcludedResource
            ADD CONSTRAINT FK_C6F7A1E1B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE Step2ExcludedResource
        ");
    }
}
