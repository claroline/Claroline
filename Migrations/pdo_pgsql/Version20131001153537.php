<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL,
                step_id INT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_867AC78073B21E9C ON innova_step2excludedResourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_867AC780B87FAB32 ON innova_step2excludedResourceNode (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResourceNode
            ADD CONSTRAINT FK_867AC78073B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step2excludedResourceNode
            ADD CONSTRAINT FK_867AC780B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2excludedResourceNode
        ");
    }
}
